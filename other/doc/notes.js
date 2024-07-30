db.getCollection('pubkeys_actual').aggregate( [
    {
      $project: {
           "length": { $strLenBytes: "$pub" }
      }
    }
  ]); /* 272 === 1024 bits; 451 === 2048 bits */
  
  printjson(db.getCollection('usage').distinct('email'));

/*2022/04/17

* check for CONTINUING session
* check for either access token non-expiration OR refresh token
* don't forget to write it in the encryption object

************* */
// working version for the more permanent table:

db.getCollection('gotokens').aggregate([
    { $match  : {'sids' :  '8f78e39a...'}}, // encrypted version
    { $project: { _id : 0,
                 expires_at: {$add:["$tgtok.created", '$tgtok.expires_in']},
                 'sid' : 1, 
                 'refresh_token' : 1,
                 'gtok.refresh_token' : 1
                }
    },
    { $match : 
        { $or :  [ {expires_at : {$gte : 1750254486 }} , {'gtok.refresh_token' : {$exists : true} } ]}
    }
    
]);

// working version sessions
db.getCollection('sessions').aggregate([
    { $match  : {'sid' : '9574ad8bc...'}}, // encrypted version
    { $project: { _id : 0,
                 expires_at: {$add:["$tgtok.created", '$tgtok.expires_in']},
                 'sid' : 1,
                 'tgtok.refresh_token' : 1,
                
                }
    },
    { $match : { $or : [{ expires_at : {$gte : 1750224486 } }, { 'tgtok.refresh_token' : { $exists : true} }        ]}}
    
])




// ****
// good enough; need to add OR refresh token
db.getCollection('sessions').aggregate([
    { $match  : {'sid' : '9574ad8...'}}, // encrypted version
    { $project: { _id : 0,
                 expires_at: {$add:["$tgtok.created", '$tgtok.expires_in']},
                 'sid' : 1, 
                }
    },
    { $match : { expires_at : {$gte : 1650224486 } }        }
    
])


// CLOSE:
db.getCollection('sessions').aggregate([
    { $project: { _id : 0,
                 difference: {$subtract:['$$NOW', "$tgtok.created"]}
                }
    }
])


// NOT YET:
db.getCollection('sessions').find({'tgtok.created' : {'$gte' : { $subtract : [0, '$tgtok.expires_in']}}})
db.getCollection('sessions').find({'tgtok.created' : {$gte : { $subtract : [0,0]}}})

db.test.aggregate(
    { $project: { _id : 0,
                 difference: {$subtract:["$f2", "$f1"]}
                }
    },
    { $match: { difference: { $lt: 100 }
              }
    })


db.test.aggregate(
    { $project: { _id : 0,
                 difference: {$subtract:[$$NOW, "$tgtok.created"]}
                }
    }
)

