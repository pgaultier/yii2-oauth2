<?php

namespace tests\unit;
use sweelix\oauth2\server\exceptions\DuplicateKeyException;
use sweelix\oauth2\server\interfaces\JwtModelInterface;
use sweelix\oauth2\server\models\Jwt;
use Yii;
/**
 * ManagerTestCase
 */
class OauthJwtStorageTest extends TestCase
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
        $jwt = Yii::createObject('sweelix\oauth2\server\interfaces\JwtModelInterface');
        /* @var JwtModelInterface $jwt */
        $jwt->clientId = 'client1';
        $jwt->subject = 'subject';
        $jwt->publicKey = 'pubKey';

        $this->assertInstanceOf(JwtModelInterface::class, $jwt);
        $this->assertTrue($jwt->save());
        $jwtId = $jwt->id;

        $jwt = Jwt::findOne(['clientId' => 'client1', 'subject' => 'subject']);
        $this->assertInstanceOf(JwtModelInterface::class, $jwt);
        $this->assertEquals($jwtId, $jwt->id);
        $this->assertEquals('client1', $jwt->clientId);
        $this->assertEquals('subject', $jwt->subject);
        $this->assertEquals('pubKey', $jwt->publicKey);

        $jwt = Yii::createObject('sweelix\oauth2\server\interfaces\JwtModelInterface');
        /* @var JwtModelInterface $jwt */
        $jwt->clientId = 'client1';
        $jwt->subject = 'subject';
        $jwt->publicKey = 'pub';
        $this->expectException(DuplicateKeyException::class);
        $jwt->save();

    }

    public function testUpdate()
    {
        $jwt = Yii::createObject('sweelix\oauth2\server\interfaces\JwtModelInterface');
        /* @var JwtModelInterface $jwt */
        $jwt->clientId = 'client1';
        $jwt->subject = 'subject';
        $jwt->publicKey = 'pubKey';

        $this->assertInstanceOf(JwtModelInterface::class, $jwt);
        $this->assertTrue($jwt->save());
        $jwtId = $jwt->id;

        $jwt = Jwt::findOne($jwtId);
        $this->assertInstanceOf(JwtModelInterface::class, $jwt);

        $jwt->subject = 'new subject';
        $this->assertTrue($jwt->save());
        $this->assertNotEquals($jwtId, $jwt->id);
        $this->assertEquals('client1', $jwt->clientId);
        $this->assertEquals('new subject', $jwt->subject);
        $this->assertEquals('pubKey', $jwt->publicKey);

        $jwtId = $jwt->id;

        $jwt->publicKey = 'new pubKey';
        $this->assertTrue($jwt->save());

        $this->assertEquals($jwtId, $jwt->id);
        $this->assertEquals('client1', $jwt->clientId);
        $this->assertEquals('new subject', $jwt->subject);
        $this->assertEquals('new pubKey', $jwt->publicKey);
    }

    public function testDelete()
    {

    }

    public function testStorage()
    {

    }
}
