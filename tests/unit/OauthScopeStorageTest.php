<?php

namespace tests\unit;
use sweelix\oauth2\server\exceptions\DuplicateKeyException;
use sweelix\oauth2\server\models\Scope;
use Yii;
/**
 * ManagerTestCase
 */
class OauthScopeStorageTest extends TestCase
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
        $scope = Yii::createObject('sweelix\oauth2\server\models\Scope');
        /* @var Scope $scope */
        $this->assertInstanceOf(Scope::className(), $scope);
        $scope->id = 'basic';
        $scope->isDefault = false;
        $scope->definition = 'Basic Scope';
        $this->assertTrue($scope->save());

        $insertedScope = Scope::findOne('basic');
        $this->assertInstanceOf(Scope::className(), $insertedScope);
        $this->assertEquals($scope->id, $insertedScope->id);
        $this->assertEquals($scope->isDefault, $insertedScope->isDefault);
        $this->assertEquals($scope->definition, $insertedScope->definition);

        $availableScopes = Scope::findAvailableScopeIds();
        $defaultScopes = Scope::findDefaultScopeIds();

        $this->assertEquals(1, count($availableScopes));
        $this->assertContains('basic', $availableScopes);

        $this->assertEquals(0, count($defaultScopes));


        $scope = Yii::createObject('sweelix\oauth2\server\models\Scope');
        /* @var Scope $scope */
        $this->assertInstanceOf(Scope::className(), $scope);
        $scope->id = 'extended';
        $scope->definition = 'Extended Scope';
        $this->assertFalse($scope->save());


        $scope = Yii::createObject('sweelix\oauth2\server\models\Scope');
        /* @var Scope $scope */
        $this->assertInstanceOf(Scope::className(), $scope);
        $scope->id = 'basic';
        $scope->isDefault = false;
        $scope->definition = 'Basic Scope';
        $this->expectException(DuplicateKeyException::class);
        $scope->save();
    }

    public function testUpdate()
    {
        $basicScope = Yii::createObject('sweelix\oauth2\server\models\Scope');
        /* @var Scope $basicScope */
        $this->assertInstanceOf(Scope::className(), $basicScope);
        $basicScope->id = 'basic';
        $basicScope->isDefault = true;
        $basicScope->definition = 'Basic Scope';
        $this->assertTrue($basicScope->save());

        $emailScope = Yii::createObject('sweelix\oauth2\server\models\Scope');
        /* @var Scope $emailScope */
        $this->assertInstanceOf(Scope::className(), $emailScope);
        $emailScope->id = 'email';
        $emailScope->isDefault = false;
        $emailScope->definition = 'Email Scope';
        $this->assertTrue($emailScope->save());

        $availableScopes = Scope::findAvailableScopeIds();
        $defaultScopes = Scope::findDefaultScopeIds();

        $this->assertEquals(2, count($availableScopes));
        $this->assertContains('basic', $availableScopes);
        $this->assertContains('email', $availableScopes);

        $this->assertEquals(1, count($defaultScopes));
        $this->assertContains('basic', $defaultScopes);

        $emailScope = Scope::findOne('email');
        $emailScope->isDefault = true;
        $this->assertTrue($emailScope->save());

        $availableScopes = Scope::findAvailableScopeIds();
        $defaultScopes = Scope::findDefaultScopeIds();

        $this->assertEquals(2, count($availableScopes));
        $this->assertContains('basic', $availableScopes);
        $this->assertContains('email', $availableScopes);

        $this->assertEquals(2, count($defaultScopes));
        $this->assertContains('basic', $defaultScopes);
        $this->assertContains('email', $defaultScopes);

        $newScope = Yii::createObject('sweelix\oauth2\server\models\Scope');
        /* @var Scope $newScope */
        $newScope->id = 'newScope';
        $newScope->isDefault = false;
        $newScope->definition = 'New scope';
        $this->assertTrue($newScope->save());

        $alteredScope = Scope::findOne('newScope');
        $alteredScope->id = 'alteredScope';
        $alteredScope->definition = 'Altered scop';
        $this->assertTrue($alteredScope->save());

        $alteredScope = Scope::findOne('alteredScope');
        $alteredScope->definition = null;
        $this->assertTrue($alteredScope->save());

        $emailScope = Scope::findOne('email');
        $emailScope->id = 'basic';
        $this->expectException(DuplicateKeyException::class);
        $emailScope->save();
    }

    public function testDelete()
    {
        $basicScope = Yii::createObject('sweelix\oauth2\server\models\Scope');
        /* @var Scope $basicScope */
        $this->assertInstanceOf(Scope::className(), $basicScope);
        $basicScope->id = 'basic';
        $basicScope->isDefault = true;
        $basicScope->definition = 'Basic Scope';
        $this->assertTrue($basicScope->save());

        $emailScope = Yii::createObject('sweelix\oauth2\server\models\Scope');
        /* @var Scope $emailScope */
        $this->assertInstanceOf(Scope::className(), $emailScope);
        $emailScope->id = 'email';
        $emailScope->isDefault = false;
        $emailScope->definition = 'Email Scope';
        $this->assertTrue($emailScope->save());

        $availableScopes = Scope::findAvailableScopeIds();
        $defaultScopes = Scope::findDefaultScopeIds();

        $this->assertEquals(2, count($availableScopes));
        $this->assertContains('basic', $availableScopes);
        $this->assertContains('email', $availableScopes);

        $this->assertEquals(1, count($defaultScopes));
        $this->assertContains('basic', $defaultScopes);

        $emailScope->delete();

        $availableScopes = Scope::findAvailableScopeIds();
        $defaultScopes = Scope::findDefaultScopeIds();

        $this->assertEquals(1, count($availableScopes));
        $this->assertContains('basic', $availableScopes);

        $this->assertEquals(1, count($defaultScopes));
        $this->assertContains('basic', $defaultScopes);

    }

    protected function cleanDatabase()
    {
        $keys = Yii::$app->redis->executeCommand('KEYS', ['oauth2:*']);
        if (empty($keys) === false) {
            Yii::$app->redis->executeCommand('DEL', $keys);
        }
    }
}
