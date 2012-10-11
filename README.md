## YiiMongoDbSuite

An Enhanced YiiMongoDbSuite which includes directmongosuite -- teamunity/yii-directmongosuite
This extension is an almost complete, ActiveRecord like support for MongoDB in Yii
It originally started as a fork of [MongoRecord](www.yiiframework.com/extension/mongorecord "MongoRecord")
extension written by [tyohan](http://www.yiiframework.com/user/31/ "tyohan"),
to fix some major bugs, and add full featured suite for [MongoDB](http://www.mongodb.org "MongoDB") developers.

## Setup

In your protected/config/main.php config file. Comment out (or delete) the current 'db' array
for your database in the components section, and add the following to the file:

## YiiMongoDbSuite Install
~~~
[php]


    'import' => array(
      ...
      'ext.YiiMongoDbSuite.*',
      'ext.YiiMongoDbSuite.components.*', // directmongosuite import Path
    ),

    'components' => array(
      'mongodb' => array(
        'class'            => 'EMongoDB',
        'connectionString' => 'mongodb://localhost',
        'dbName'           => 'myDatabaseName',
        'fsyncFlag'        => true,
        'safeFlag'         => true,
        'useCursor'        => false
      ),
    ),


~~~

- ConnectionString: 'localhost' should be changed to the ip or hostname of your host being connected to. For example
  if connecting to a server it might be `'connectionString' => 'mongodb://username@xxx.xx.xx.xx'` where xx.xx.xx.xx is
  the ip (or hostname) of your webserver or host.
- dbName: is the name you want the collections to be stored in. The database name.
- fsyncFlag if is set to true, this makes mongodb make sure all writes to the database are safely stored to disk. (as default, true)
- safeFlag if is set to true, mongodb will wait to retrieve status of all write operations, and check if everything went OK. (as default, true)
- useCursors if is set to true, extension will return EMongoCursor instead of raw pre-populated arrays, form findAll* methods, (default to false, for backwards compatibility)

## directmongosuite install
- The minimal configuration with the default settings in config/main.php
- Add the Application behavior component

~~~
[php]

'behaviors' => array(
                      'edms' => array(
                        'class'=>'EDMSBehavior',
 
                        'connectionId' = 'mongodb', //if you work with yiimongodbsuite 
 
                        //see the application component 'EDMSConnection' below
                        // 'connectionId' = 'edms', //default;
                        //'debug'=>true //for extended logging
                      )
            ),

~~~


- Add the other application components under 'components'

~~~
[php]

// application components
    'components'=>array(

        //uses the collection 'edms_authmanager' for the authmanager
        'authManager'=>array(
            'class'=>'EDMSAuthManager',
            'connectionId' => 'mongodb',
        ),

        //Configuring the mongodb connection using EDMSConnection is only needed if not using YiiMongoDbSuite
        //configure the mongodb connection
        //set the values for server and options analog to the constructor 
        //Mongo::__construct from the PHP manual
        //'edms' => array(
        //    'class'            => 'EDMSConnection',
        //    'dbName'           => 'testdb',
            //'server'           => 'mongodb://localhost:27017' //default
            //'options'  => array(.....); 
        //),
        
        //manage the httpsession in the collection 'edms_httpsession'
        //Not Sure How Baller EDMSHttpSession is, or maybe i'm not baller enough
        //'session'=>array(
                        //'class'=>'EDMSHttpSession',
                        //set this explizit if you want to switch servers/databases
                        //See below: Switching between servers and databases                        
                        //'connectionId'=>'edms',
                        //'dbName'=>'testdb',
                   // ),
 
        //manage the cache in the collection 'edms_cache'
        'cache' => array(
            'class'=>'EDMSCache',    
            //set to false after first use of the cache to increase performance
            'ensureIndex' => true,
 
            //Maybe set connectionId and dbName too: see Switching between servers and databases 
        ),
 
        //log into the collection 'edms_log'
        'log'=>array(
            'class'=>'CLogRouter',
            'routes'=>array(
                array(
                    'class'=>'EDMSLogRoute',
                      'levels'=>'trace, info, error, warning, edms', //add the level edms
                      //Maybe set connectionId and dbName too: see Switching between servers and databases 
                    ),
            ),
        ),
~~~

## directmongosuite Usage

Dataprovider 

The directmongosuite comes with the EDMSDataprovider. So you can render the 'find' results of a mongoDB query into standard Yii components: CListview ...

The constructor needs the MongoCursor after a find operation and supports 'sort' and 'pagination'.

~~~
[php]

$cursor = Yii::app()->edmsMongoCollection('members')->find($criteria,$select);
//or
$cursor = EDMSQuery::instance('members')->findCursor($criteria,$select);
 
$dataProvider = new EDMSDataProvider($cursor,
            array(
                     'sort'=>array('create_time'=>-1),  //desc
                     'pagination'=>array(
                          'pageSize'=>20,
                        ),
                     ));
 
var_dump($dataProvider->getData());

~~~


- The dataprovider above returns the rows as arrays, like the CArrayDataProvider. But if you need/want to get an array of standardobject or even models as data you can set the third constructor param '$objectClassName' or better use the EDMSQuery:

~~~
[php]

//the same as above: $config is the configarray for the dataprovider with sort, pagination ...
$dataProvider = EDMSQuery::instance('members')->getArrayDataProvider($criteria,$select,$config);
 
//the data as array of 'stdClass' objects
$dataProvider = EDMSQuery::instance('members')->getObjectDataProvider($criteria,$select,$config);
 
//the data as array of models (instances of the class 'ContactForm')
$dataProvider = EDMSQuery::instance('members')->getModelDataProvider('ContactForm',$criteria,$select,$config);

~~~

That's all you have to do for setup. You can use it very much like the active record.


