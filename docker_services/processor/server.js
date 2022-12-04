const express = require('express');
const Redis = require('ioredis');
    
const mysql_config = {
    host: 'localhost',
    user: 'root',
    password: 'root',
    database: 'shopify_project',
    port: 3306
}

const redis_config = {
    host: 'localhost',
    port: 6379
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
        console.log(NODE_ENV+' Testing redis connection (%s)...', redis_config.host);
        await redisClient.ping();
        console.log('✅');
        redisClient = null;
    }
    catch (err) {
        console.error('❌');
        console.log(err.message);
        process.exit(1);
    }

    const Database = require('./src/Database.class');

    try {
        const db = new Database(mysql_config);
        // ping mysql endpoint
        console.log(NODE_ENV+' Testing MySQL connection (%s)...', mysql_config.host);
        await db.ping();
        console.log('✅');
    }
    catch (err) {
        console.error('❌');
        console.log(err.message);
        process.exit(1);
    }

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