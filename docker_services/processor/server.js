const express       = require('express');
const Elasticsearch = require('@elastic/elasticsearch');
const Redis         = require('ioredis');
const ESHandler     = require('./src/ElasticSearch.class');
const Database      = require('./src/Database.class');
    
const mysql_config = {
    host: 'localhost',
    user: 'root',
    password: 'root',
    database: 'shopify_project',
    port: 3306
}
const db = new Database(mysql_config);

const redis_config = {
    host: 'localhost',
    port: 6379
}

const es_config = {
    node: 'http://localhost:9200',
    maxRetries: 5,
    requestTimeout: 60000,
    sniffOnStart: false
}

process.on('warning', e => console.warn(e.stack));

const NODE_ENV = process.env.NODE_ENV || null;
console.log('NODE_ENV: %s', NODE_ENV);

(async () => {
    const app = express();
    const port = 8010;
    
    try {
        // ping Redis
        let redisClient = new Redis(redis_config);
        console.log('Testing redis connection (%s)...', redis_config.host);
        await redisClient.ping();
        console.log('✅');
        redisClient = null;
    }
    catch (err) {
        console.error('❌');
        console.log(err.message);
        process.exit(1);
    }

    try {
        // ping mysql endpoint
        console.log('Testing MySQL connection (%s)...', mysql_config.host);
        await db.ping();
        console.log('✅');
    }
    catch (err) {
        console.error('❌');
        console.log(err.message);
        process.exit(1);
    }

    try {
        // ping Elasticsearch
        console.log('Testing ElasticSearch connection (%s)...', es_config.node);
        let elasticClient = new Elasticsearch.Client(es_config);
        await elasticClient.ping();
        console.log('✅');
        elasticClient = null;
    }
    catch (err) {
        console.error('❌');
        console.log(err.message);
        process.exit(1);
    }

    const es_handler = new ESHandler(es_config);

    function sendResp(res, status, json = {}) {
        return res.status(status).json(json);
    }

    const method = {
        get: (req, res, next) => {
            if (req.method !== 'GET') {
                return sendResp(req, res, 405);
            }
            return next();
        },
        post: (req, res, next) => {
            if (req.method !== 'POST') {
                return sendResp(req, res, 405);
            }
            return next();
        },
        put: (req, res, next) => {
            if (req.method !== 'PUT') {
                return sendResp(req, res, 405);
            }
            return next();
        }
    }

    app.use('/ping/processor', method.get, async (req, res) => {
        return sendResp(res, 200, {"status": 'ok', "message": "In processor container"});
    });

    app.use('/index/elasticsearch', method.get, async (req, res) => {
        
        try {
            const stores = await db.getStores();
            for(var i in stores) {
                var insertBody = { 
                    id: stores[i].id,
                    name: stores[i].name,
                    domain: stores[i].myshopify_domain,
                    access_token: stores[i].access_token,
                    phone: stores[i].phone,
                    address1: stores[i].address1,
                    address2: stores[i].address2,
                    zip: stores[i].zip,
                    created_at: stores[i].created_at,
                };
                await es_handler.indexInsert(stores[i].id, insertBody, 'stores');
            }
        } catch(err) {
            console.log(err.message);
            return sendResp(res, 400, {'status': false, 'message': err.message})
        }
        return sendResp(res, 200, {"status": true, "message": "Stores Indexed"});
    });

    app.use('/search/store', method.get, async(req, res) => {
        try{
            const searchTerm = req.query.search;
            const response = await es_handler.searchQuery(searchTerm);
            return sendResp(res, 200, response.body.hits);
        } catch(err) {
            console.log(err.message);
            return sendResp(res, 400, {'status': false, 'message': err.message});
        }
    });

    app.use(method.get, (req, res) => {
        if (req.path === '/') {
            return sendResp(req, res, 200)
        } else {
            return sendResp(req, res, 404);
        }
    });

    app.listen(port);
    console.log(`Server running on port ${port}`);
})();