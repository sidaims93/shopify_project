const Elasticsearch = require('@elastic/elasticsearch');

class ElasticSearch {

    constructor(config) {
        this.elasticsearch   = new Elasticsearch.Client(config);
    }
    
    // insert document into elasticsearch (fails if it exists)
    async indexInsert(docId, docBody, indexName) {
        
        return this.elasticsearch.index({
            index: indexName,
            id: docId,
            opType: 'create',
            body: docBody,
            refresh: false
        }, 
        {
            ignore: [ 409 ],
            maxRetries: 3
        });

    }

    async searchQuery(searchTerm) {
        
        return this.elasticsearch.search({
            index: 'stores',
            body:{
                query: {
                    query_string: {
                        fields: ["name", "domain", "address1", "address2"],
                        query: searchTerm+'*'
                    }
                }
            }
        });

    }
}

module.exports = ElasticSearch;

