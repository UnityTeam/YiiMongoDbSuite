yii-directmongosuite
====================

The directmongosuite extension for Yii Framework.



More Unity 
======

Leverage Your YiiMongoDBSuite Models and Connections, to speed up non-crud bulk operations.


Notes
=================

We have renamed the original directory from "directmongosuite" to "yii-directmongosuite".

The original author says that directmongosuite is "tested in yii 1.1.8", but he did not provide any test suite...we will add our own soon...







Original Authors Readme
========================
http://www.yiiframework.com/user/6785/ 
http://www.yiiframework.com/extension/directmongosuite

Why another 'suite' for the mongoDB if there exists the extension yiimongodbsuite?

Two reasons: 

yiimongodbsuite is great and saves a lot of work when you need models and UI for CRUD operations. But the sideeffect is sometimes a poor performance (because all the overhead of AR behind) and you are not really free to make use of the schema-less property of mongoDB. So in my project I always worked with direct mongoDB operations too and implemented some little helpers to speed up working with the mongoDB.
Beside the yiimongodbsuite there exists a few extensions (cache, session ...) for the mongoDB. But you have to download and install all these utils separately and - that's the problem - you have to configure the server/database extra for each of these extensions.
So this extension comes with following extensions in one package, modified (and renamed). All components (by default) use the same connection component.

Integrated components 

Extensions by aoyagikouhei

mongodbhttpsession (now EDMSHttpSession)
mongodblogroute (now EDMSLogRoute)
My published extensions

mongodblogviewer (now EDMSLogViewer)
mongodbcache (now EDMSCache)
mongodbauthmanager (now EDMSAuthManager)
and some new helper components

EDMSBehavior
EDMSConnection
EDMSDataprovider
EDMSQuery
EDMSLogAction
EDMSSequence (since v0.2)
Requirements 

tested with Yii 1.1.8, but should work with 1.1.6+ too
PHP mongoDB driver installed