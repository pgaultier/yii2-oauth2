<?php

namespace tests\unit;

use OAuth2\Storage\JwtBearerInterface;
use sweelix\oauth2\server\exceptions\DuplicateKeyException;
use sweelix\oauth2\server\interfaces\JtiModelInterface;
use sweelix\oauth2\server\models\Jti;
use Yii;

/**
 * ManagerTestCase
 */
class OauthJtiStorageTestCase extends TestCase
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
        $date = time() + 1250;
        $jti = Yii::createObject('sweelix\oauth2\server\interfaces\JtiModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\JtiModelInterface $jti */
        $jti->clientId = 'client1';
        $jti->subject = 'subject';
        $jti->audience = 'audience';
        $jti->expires = $date;
        $jti->jti = 'Real jti data';
        $this->assertTrue($jti->save());

        $jti = Yii::createObject('sweelix\oauth2\server\interfaces\JtiModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\JtiModelInterface $jti */
        $jti->clientId = 'client2';
        $jti->audience = 'audience';
        $jti->expires = $date;
        $jti->jti = 'Real jti data';
        $this->assertFalse($jti->save());

        $jti = Yii::createObject('sweelix\oauth2\server\interfaces\JtiModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\JtiModelInterface $jti */
        $jti->clientId = 'client1';
        $jti->subject = 'subject';
        $jti->audience = 'audience';
        $jti->expires = $date;
        $jti->jti = 'Real jti data';
        $this->expectException(DuplicateKeyException::class);
        $jti->save();
    }

    public function testUpdate()
    {
        $date = time() + 1250;
        $jti = Yii::createObject('sweelix\oauth2\server\interfaces\JtiModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\JtiModelInterface $jti1 */
        $jti->clientId = 'client1';
        $jti->subject = 'subject';
        $jti->audience = 'audience';
        $jti->expires = $date;
        $jti->jti = 'Real jti data';
        $this->assertTrue($jti->save());
        $jtiId = $jti->id;

        $jti = Jti::findOne($jtiId);
        $this->assertInstanceOf(JtiModelInterface::class, $jti);
        $this->assertEquals('client1', $jti->clientId);
        $this->assertEquals('subject', $jti->subject);
        $this->assertEquals('audience', $jti->audience);
        $this->assertEquals($date, $jti->expires);
        $this->assertEquals('Real jti data', $jti->jti);

        $jti = Jti::findOne(['clientId' => 'client1', 'subject' => 'subject', 'audience' => 'audience', 'expires' => $date, 'jti' => 'Real jti data']);
        $this->assertInstanceOf(JtiModelInterface::class, $jti);
        $this->assertEquals('client1', $jti->clientId);
        $this->assertEquals('subject', $jti->subject);
        $this->assertEquals('audience', $jti->audience);
        $this->assertEquals($date, $jti->expires);
        $this->assertEquals('Real jti data', $jti->jti);
        $this->assertEquals($jtiId, $jti->id);

        $jti->subject = null;
        $this->assertFalse($jti->save());
        $jti->subject = 'new subject';
        $this->assertTrue($jti->save());
        $this->assertNotEquals($jtiId, $jti->id);

        $jti = Yii::createObject('sweelix\oauth2\server\interfaces\JtiModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\JtiModelInterface $jti */
        $jti->clientId = 'client1';
        $jti->subject = 'subject';
        $jti->audience = 'audience';
        $jti->expires = $date;
        $jti->jti = 'Real jti data';
        $this->assertTrue($jti->save());

        $jti = Yii::createObject('sweelix\oauth2\server\interfaces\JtiModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\JtiModelInterface $jti */
        $jti->clientId = 'client2';
        $jti->subject = 'subject';
        $jti->audience = 'audience';
        $jti->expires = $date;
        $jti->jti = 'Real jti data';
        $this->assertTrue($jti->save());

        $jti->clientId = 'client1';
        $this->expectException(DuplicateKeyException::class);
        $jti->save();
    }

    public function testDelete()
    {
        $date = time() + 1250;
        $jti = Yii::createObject('sweelix\oauth2\server\interfaces\JtiModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\JtiModelInterface $jti */
        $jti->clientId = 'client1';
        $jti->subject = 'subject';
        $jti->audience = 'audience';
        $jti->expires = $date;
        $jti->jti = 'Real jti data';
        $this->assertTrue($jti->save());
        $jtiId = $jti->id;

        $jti = Jti::findOne($jtiId);
        $this->assertInstanceOf(JtiModelInterface::class, $jti);
        $this->assertTrue($jti->delete());

        $jti = Jti::findOne($jtiId);
        $this->assertNull($jti);

        $jti = Yii::createObject('sweelix\oauth2\server\interfaces\JtiModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\JtiModelInterface $jti */
        $jti->clientId = 'client1';
        $jti->subject = 'subject';
        $jti->audience = 'audience';
        $jti->expires = $date;
        $jti->jti = 'Real jti data';
        $this->assertTrue($jti->save());

        $jti = Jti::findOne(['clientId' => 'client1', 'subject' => 'subject', 'audience' => 'audience', 'expires' => $date, 'jti' => 'Real jti data']);
        $this->assertInstanceOf(JtiModelInterface::class, $jti);
        $this->assertTrue($jti->delete());

        $jti = Jti::findOne(['clientId' => 'client1', 'subject' => 'subject', 'audience' => 'audience', 'expires' => $date, 'jti' => 'Real jti data']);
        $this->assertNull($jti);
    }

    public function testStorage()
    {
        $date = time() + 1250;
        $storage = Yii::createObject('sweelix\oauth2\server\storage\OauthStorage');
        /* @var JwtBearerInterface $storage */
        $this->assertInstanceOf(JwtBearerInterface::class, $storage);

        $jti = Yii::createObject('sweelix\oauth2\server\interfaces\JtiModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\JtiModelInterface $jti1 */
        $jti->clientId = 'client2';
        $jti->subject = 'subject';
        $jti->audience = 'audience';
        $jti->expires = $date;
        $jti->jti = 'Real jti data';
        $this->assertTrue($jti->save());

        $storage->setJti('client1', 'subject', 'audience', $date, 'Real jti data');

        $jti = Jti::findOne(['clientId' => 'client1', 'subject' => 'subject', 'audience' => 'audience', 'expires' => $date, 'jti' => 'Real jti data']);
        $this->assertInstanceOf(JtiModelInterface::class, $jti);

        $this->assertEquals('client1', $jti->clientId);
        $this->assertEquals('subject', $jti->subject);
        $this->assertEquals('audience', $jti->audience);
        $this->assertEquals($date, $jti->expires);
        $this->assertEquals('Real jti data', $jti->jti);

        $jtiData = $storage->getJti('client1', 'subject', 'audience', $date, 'Real jti data');

        $this->assertEquals('client1', $jtiData['issuer']);
        $this->assertEquals('subject', $jtiData['subject']);
        $this->assertEquals('audience', $jtiData['audience']);
        $this->assertEquals($date, $jtiData['expires']);
        $this->assertEquals('Real jti data', $jtiData['jti']);

        $this->expectException(DuplicateKeyException::class);
        $storage->setJti('client2', 'subject', 'audience', $date, 'Real jti data');
    }
}
