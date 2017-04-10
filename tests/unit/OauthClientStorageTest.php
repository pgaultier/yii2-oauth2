<?php

namespace tests\unit;
use OAuth2\Storage\ClientCredentialsInterface;
use sweelix\oauth2\server\exceptions\DuplicateKeyException;
use sweelix\oauth2\server\interfaces\ClientModelInterface;
use sweelix\oauth2\server\models\Client;
use Yii;
/**
 * ManagerTestCase
 */
class OauthClientStorageTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication([
        ]);
        $this->cleanDatabase();
    }
    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testInsert()
    {
        $client = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        /* @var ClientModelInterface $client */
        $this->assertInstanceOf(ClientModelInterface::class, $client);
        $client->id = 'client1';
        $client->secret = 'secret1';
        $client->isPublic = true;
        $client->grantTypes = [];
        $client->redirectUri = 'http://sweelix.net';
        $client->userId = 'uid';
        $client->scopes = [];
        $client->name = 'Test client';

        $this->assertTrue($client->save());

        $insertedClient = Client::findOne('client1');
        $this->assertInstanceOf(ClientModelInterface::class, $insertedClient);
        $this->assertEquals($client->id, $insertedClient->id);
        $this->assertEquals($client->secret, $insertedClient->secret);
        $this->assertEquals($client->isPublic, $insertedClient->isPublic);
        $this->assertTrue(is_array($client->grantTypes));
        $this->assertTrue(empty($client->grantTypes));
        $this->assertTrue(is_array($client->redirectUri));
        $this->assertEquals($client->redirectUri[0], $insertedClient->redirectUri[0]);
        $this->assertEquals($client->userId, $insertedClient->userId);
        $this->assertTrue(is_array($client->scopes));
        $this->assertTrue(empty($client->scopes));
        $this->assertEquals($client->name, $insertedClient->name);

        $client = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        /* @var ClientModelInterface $client */
        $this->assertInstanceOf(ClientModelInterface::class, $client);
        $client->id = 'scopedClient';
        $client->secret = 'secret2';
        $client->isPublic = true;
        $client->grantTypes = [];
        $client->redirectUri = 'http://sweelix.net';
        $client->userId = 'uid';
        $client->scopes = ['basic'];
        $client->name = 'Test client';

        $this->assertFalse($client->save());
        $this->assertTrue($client->hasErrors('scopes'));

        $this->populateScopes();

        $this->assertTrue($client->save());


        $client = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        /* @var ClientModelInterface $client */
        $this->assertInstanceOf(ClientModelInterface::class, $client);
        $client->id = 'client2';
        $client->name = 'Test Client 2';
        $this->assertFalse($client->save());


        $client = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        /* @var ClientModelInterface $client */
        $this->assertInstanceOf(ClientModelInterface::class, $client);
        $client->id = 'client1';
        $client->secret = 'secret1';
        $client->isPublic = true;
        $client->grantTypes = [];
        $client->userId = 'uid';
        $client->scopes = [];
        $client->name = 'Test client';
        $this->expectException(DuplicateKeyException::class);
        $client->save();
    }

    public function testUpdate()
    {
        $client1 = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        /* @var ClientModelInterface $client1 */
        $this->assertInstanceOf(ClientModelInterface::class, $client1);
        $client1->id = 'client1';
        $client1->secret = 'secret1';
        $client1->isPublic = true;
        $client1->grantTypes = [];
        $client1->userId = 'uid';
        $client1->scopes = [];
        $client1->name = 'Test client';
        $this->assertTrue($client1->save());

        $client2 = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        /* @var ClientModelInterface $client2 */
        $this->assertInstanceOf(ClientModelInterface::class, $client2);
        $client2->id = 'client2';
        $client2->secret = 'secret1';
        $client2->isPublic = true;
        $client2->grantTypes = [];
        $client2->userId = 'uid';
        $client2->scopes = ['basic'];
        $client2->name = 'Test client';
        $this->assertFalse($client2->save());
        $this->populateScopes();
        $this->assertTrue($client2->save());

        $client2 = Client::findOne('client2');
        $client2->secret = 'secret2';
        $status = $client2->save();
        $this->assertTrue($status);

        $client2 = Client::findOne('client2');
        $client2->id = 'client3';
        $client2->name = null;
        $this->assertTrue($client2->save());

        $client2->id = 'client2';
        $this->assertTrue($client2->save());

        $client2 = Client::findOne('client2');
        $client2->id = 'client1';
        $this->expectException(DuplicateKeyException::class);
        $client2->save();
    }

    public function testDelete()
    {
        $client = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        /* @var ClientModelInterface $client */
        $this->assertInstanceOf(ClientModelInterface::class, $client);
        $client->id = 'client1';
        $client->secret = 'secret1';
        $client->isPublic = true;
        $client->userId = 'uid';
        $client->name = 'Test client';
        $this->assertTrue($client->save());

        $client->delete();
        $deletedClient = Client::findOne('client1');
        $this->assertNull($deletedClient);

    }

    public function testStorage()
    {
        $client = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        /* @var Client $client */
        $this->assertInstanceOf(ClientModelInterface::class, $client);
        $client->id = 'client1';
        $client->secret = 'secret1';
        $client->isPublic = true;
        $client->userId = 'uid';
        $client->name = 'Test client';
        $this->assertTrue($client->save());

        $storage = Yii::createObject('sweelix\oauth2\server\storage\OauthStorage');
        /* @var ClientCredentialsInterface $storage */
        $this->assertInstanceOf(ClientCredentialsInterface::class, $storage);

        $storageClient = $storage->getClientDetails('fail');
        $this->assertFalse($storageClient);
        $storageClient = $storage->getClientDetails('client1');
        $this->assertArrayHasKey('client_id', $storageClient);
        $this->assertArrayHasKey('redirect_uri', $storageClient);
        $this->assertArrayHasKey('grant_types', $storageClient);
        $this->assertArrayHasKey('user_id', $storageClient);
        $this->assertArrayHasKey('scope', $storageClient);
        $this->assertEquals($client->id, $storageClient['client_id']);
        $this->assertTrue(is_array($client->redirectUri));
        $this->assertEquals(implode(' ', $client->redirectUri), $storageClient['redirect_uri']);
        $this->assertTrue(is_array($storageClient['grant_types']));
        $this->assertTrue(empty($storageClient['grant_types']));
        $this->assertEquals($client->userId, $storageClient['user_id']);
        $this->assertEquals(implode(' ', $client->scopes), $storageClient['scope']);

        $scope = $storage->getClientScope('client1');
        $this->assertEquals('', $scope);

        $isPublic = $storage->isPublicClient('client1');
        $this->assertTrue($isPublic);
        $hasCredentials = $storage->checkClientCredentials('client1');
        $this->assertFalse($hasCredentials);
        $hasCredentials = $storage->checkClientCredentials('client1', 'secret1');
        $this->assertTrue($hasCredentials);

        $client = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        /* @var Client $client */
        $this->assertInstanceOf(ClientModelInterface::class, $client);
        $client->id = 'client2';
        $client->secret = 'secret2';
        $client->isPublic = false;
        $client->userId = 'uid';
        $client->name = 'Test client 2';
        $this->assertTrue($client->save());

        $isPublic = $storage->isPublicClient('client2');
        $this->assertFalse($isPublic);

        $isRestricted = $storage->checkRestrictedGrantType('client2', 'password');
        $this->assertTrue($isRestricted);
        $client->grantTypes = ['password'];
        $this->assertTrue($client->save());
        $isRestricted = $storage->checkRestrictedGrantType('client2', 'password');
        $this->assertTrue($isRestricted);
        $isRestricted = $storage->checkRestrictedGrantType('client2', 'code');
        $this->assertFalse($isRestricted);

        $this->populateScopes();

    }

    public function testUser()
    {
        $client1 = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        /* @var ClientModelInterface $client1 */
        $this->assertInstanceOf(ClientModelInterface::class, $client1);
        $client1->id = 'client1';
        $client1->secret = 'secret1';
        $client1->isPublic = true;
        $client1->grantTypes = [];
        $client1->userId = 'uid';
        $client1->scopes = [];
        $client1->name = 'Test client';
        $this->assertTrue($client1->save());

        $client2 = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        /* @var ClientModelInterface $client2 */
        $this->assertInstanceOf(ClientModelInterface::class, $client2);
        $client2->id = 'client2';
        $client2->secret = 'secret1';
        $client2->isPublic = true;
        $client2->grantTypes = [];
        $client2->userId = 'uid';
        $client2->scopes = [];
        $client2->name = 'Test client';
        $this->assertTrue($client2->save());

        // client1 ->
        // client2 -> user1, user2
        $this->assertTrue($client2->addUser('user1'));
        $this->assertTrue($client2->addUser('user2'));

        $this->assertFalse($client1->hasUser('user1'));
        $this->assertTrue($client2->hasUser('user1'));
        $this->assertTrue($client2->hasUser('user2'));

        $user1Clients = Client::findAllByUserId('user1');
        $this->assertTrue(is_array($user1Clients));
        $this->assertEquals(1, count($user1Clients));

        // client1 ->
        // client2 -> user2
        $this->assertTrue($client2->removeUser('user1'));
        $this->assertFalse($client2->hasUser('user1'));

        $user2Clients = Client::findAllByUserId('user2');
        $this->assertTrue(is_array($user2Clients));
        $this->assertEquals(1, count($user2Clients));

        // client1 -> user1, user2
        // client2 -> user2
        $this->assertTrue($client1->addUser('user1'));
        $this->assertTrue($client1->addUser('user2'));

        $user2Clients = Client::findAllByUserId('user2');
        $this->assertTrue(is_array($user2Clients));
        $this->assertEquals(2, count($user2Clients));


    }
}
