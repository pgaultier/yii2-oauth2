<?php

namespace tests\unit;

use OAuth2\Storage\PublicKeyInterface;
use sweelix\oauth2\server\exceptions\DuplicateKeyException;
use sweelix\oauth2\server\interfaces\CypherKeyModelInterface;
use sweelix\oauth2\server\models\CypherKey;
use Yii;

/**
 * ManagerTestCase
 */
class OauthCypherKeyStorageTestCase extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication([
        ]);
        $this->cleanDatabase();
        $this->populateClients();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testInsert()
    {
        $cypherKey = Yii::createObject('sweelix\oauth2\server\interfaces\CypherKeyModelInterface');
        /* @var CypherKeyModelInterface $cypherKey */
        $this->assertInstanceOf(CypherKeyModelInterface::class, $cypherKey);
        $cypherKey->id = 'client1';
        $cypherKey->generateKeys();
        $publicKey = $cypherKey->publicKey;
        $privateKey = $cypherKey->privateKey;
        $this->assertTrue($cypherKey->save());

        $cypherKey = CypherKey::findOne('client1');
        $this->assertEquals('client1', $cypherKey->id);
        $this->assertEquals($publicKey, $cypherKey->publicKey);
        $this->assertEquals($privateKey, $cypherKey->privateKey);
        $this->assertEquals(CypherKey::HASH_ALGO, $cypherKey->encryptionAlgorithm);

        $cypherKey = Yii::createObject('sweelix\oauth2\server\interfaces\CypherKeyModelInterface');
        /* @var CypherKeyModelInterface $cypherKey */
        $this->assertInstanceOf(CypherKeyModelInterface::class, $cypherKey);
        $cypherKey->id = 'client2';
        $this->assertFalse($cypherKey->save());

        $cypherKey = Yii::createObject('sweelix\oauth2\server\interfaces\CypherKeyModelInterface');
        /* @var CypherKeyModelInterface $cypherKey */
        $this->assertInstanceOf(CypherKeyModelInterface::class, $cypherKey);
        $cypherKey->id = 'client1';
        $cypherKey->generateKeys();
        $this->expectException(DuplicateKeyException::class);
        $cypherKey->save();
    }

    public function testUpdate()
    {
        $cypherKey = Yii::createObject('sweelix\oauth2\server\interfaces\CypherKeyModelInterface');
        /* @var CypherKeyModelInterface $cypherKey */
        $this->assertInstanceOf(CypherKeyModelInterface::class, $cypherKey);
        $cypherKey->id = 'client1';
        $cypherKey->generateKeys();
        $this->assertTrue($cypherKey->save());

        $cypherKey = CypherKey::findOne('client1');
        $cypherKey->privateKey = 'private';
        $cypherKey->publicKey = 'public';
        $this->assertTrue($cypherKey->save());

        $cypherKey->publicKey = null;
        $this->assertFalse($cypherKey->save());
        $cypherKey->id = 'client2';
        $cypherKey->publicKey = 'pub';
        $this->assertTrue($cypherKey->save());

        $cypherKey = Yii::createObject('sweelix\oauth2\server\interfaces\CypherKeyModelInterface');
        /* @var CypherKeyModelInterface $cypherKey */
        $this->assertInstanceOf(CypherKeyModelInterface::class, $cypherKey);
        $cypherKey->id = 'client1';
        $cypherKey->generateKeys();
        $this->assertTrue($cypherKey->save());

        $cypherKey = CypherKey::findOne('client2');
        $this->assertEquals('client2', $cypherKey->id);
        $this->assertEquals('private', $cypherKey->privateKey);
        $this->assertEquals('pub', $cypherKey->publicKey);
        $this->assertEquals(CypherKey::HASH_ALGO, $cypherKey->encryptionAlgorithm);

        $cypherKey->id = 'client1';
        $this->expectException(DuplicateKeyException::class);
        $cypherKey->save();
    }

    public function testDelete()
    {
        $cypherKey = Yii::createObject('sweelix\oauth2\server\interfaces\CypherKeyModelInterface');
        /* @var CypherKeyModelInterface $cypherKey */
        $this->assertInstanceOf(CypherKeyModelInterface::class, $cypherKey);
        $cypherKey->id = 'client1';
        $cypherKey->generateKeys();
        $this->assertTrue($cypherKey->save());

        $cypherKey = CypherKey::findOne('client1');
        $this->assertInstanceOf(CypherKeyModelInterface::class, $cypherKey);

        $this->assertTrue($cypherKey->delete());

        $cypherKey = CypherKey::findOne('client1');
        $this->assertNull($cypherKey);
    }

    public function testStorage()
    {
        $cypherKey = Yii::createObject('sweelix\oauth2\server\interfaces\CypherKeyModelInterface');
        /* @var CypherKeyModelInterface $cypherKey */
        $this->assertInstanceOf(CypherKeyModelInterface::class, $cypherKey);
        $cypherKey->id = 'default';
        $cypherKey->generateKeys();
        $defaultPublicKey = $cypherKey->publicKey;
        $defaultPrivateKey = $cypherKey->privateKey;
        $this->assertTrue($cypherKey->save());

        $cypherKey = Yii::createObject('sweelix\oauth2\server\interfaces\CypherKeyModelInterface');
        /* @var CypherKeyModelInterface $cypherKey */
        $this->assertInstanceOf(CypherKeyModelInterface::class, $cypherKey);
        $cypherKey->id = 'client1';
        $cypherKey->generateKeys();
        $clientPublicKey = $cypherKey->publicKey;
        $clientPrivateKey = $cypherKey->privateKey;
        $cypherKey->encryptionAlgorithm = 'RS512';
        $this->assertTrue($cypherKey->save());

        $storage = Yii::createObject('sweelix\oauth2\server\storage\OauthStorage');
        /* @var PublicKeyInterface $storage */
        $this->assertInstanceOf(PublicKeyInterface::class, $storage);

        $publicKey = $storage->getPublicKey();
        $this->assertEquals($defaultPublicKey, $publicKey);
        $privateKey = $storage->getPrivateKey();
        $this->assertEquals($defaultPrivateKey, $privateKey);
        $encryptionAlgorithm = $storage->getEncryptionAlgorithm();
        $this->assertEquals('RS256', $encryptionAlgorithm);

        $publicKey = $storage->getPublicKey('default');
        $this->assertEquals($defaultPublicKey, $publicKey);
        $privateKey = $storage->getPrivateKey('default');
        $this->assertEquals($defaultPrivateKey, $privateKey);
        $encryptionAlgorithm = $storage->getEncryptionAlgorithm('default');
        $this->assertEquals('RS256', $encryptionAlgorithm);

        $publicKey = $storage->getPublicKey('client2');
        $this->assertEquals($defaultPublicKey, $publicKey);
        $privateKey = $storage->getPrivateKey('client2');
        $this->assertEquals($defaultPrivateKey, $privateKey);
        $encryptionAlgorithm = $storage->getEncryptionAlgorithm('client2');
        $this->assertEquals('RS256', $encryptionAlgorithm);

        $publicKey = $storage->getPublicKey('client1');
        $this->assertEquals($clientPublicKey, $publicKey);
        $privateKey = $storage->getPrivateKey('client1');
        $this->assertEquals($clientPrivateKey, $privateKey);
        $encryptionAlgorithm = $storage->getEncryptionAlgorithm('client1');
        $this->assertEquals('RS512', $encryptionAlgorithm);
    }
}
