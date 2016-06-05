<?php

require '../../vendor/autoload.php';

require 'User.php';
require 'Comment.php';
require 'Skill.php';
require 'Profile.php';

use TRW\ActiveRecord\BaseRecord;
use TRW\ActiveRecord\Database\Driver\MySql;

use App\Model\User;
use App\Model\Profile;
use App\Model\Skill;
use App\Model\Comment;


class RecordAssociationTest extends PHPUnit_Framework_TestCase {

	protected static $connection;

	public static function setUpBeforeClass() {
		$config = require '../config.php';
		self::$connection = new MySql($config['Database']['MySql']);
		BaseRecord::setConnection(self::$connection);
	}

	public function setUp(){
		$conn = self::$connection;
		TRW\ActiveRecord\IdentityMap::clearAll();
		$conn->query("DELETE FROM users");
		$conn->query("INSERT INTO users(id,name) VALUES(1,'bar'), (2, 'foo'), (3, 'hoge'), (4, 'fuga')");
		
		$conn->query("DELETE FROM comments");
		$conn->query("INSERT INTO comments(id, text, user_id)
			VALUES(1, 'bar comment first', 1), (2, 'bar comment second', 1), (3, 'foo comment first', 2)");

		$conn->query("DELETE FROM users_skills");
		$conn->query("INSERT INTO users_skills(id, users_id, skills_id) 
			VALUES(1, 1, 1), (2, 1, 2), (3, 2, 1)");
	
		$conn->query("DELETE FROM skills");
		$conn->query("INSERT INTO skills(id, name)
			 VALUES(1,'skill1'), (2, 'skill2'), (3, 'skill3')");

		$conn->query("DELETE FROM profiles");
		$conn->query("INSERT INTO profiles(id, text, user_id) 
			VALUES(1, 'bar profile', 1), (2, 'foo frofile', 2), (3, 'hoge frofile', 3)");
	}


	public  function testHasOneRead(){
		$user = User::read(1);

		$this->assertEquals('bar', $user->name);
		$this->assertEquals('bar profile', $user->Profile->text);
		$this->assertEquals($user->id, $user->Profile->user_id);
	}


	public function testHasManyRead(){
		$user1 = User::read(1);

		$this->assertEquals('bar', $user1->name);
		$this->assertEquals('bar comment first', $user1->Comment[0]->text);
		$this->assertEquals('bar comment second', $user1->Comment[1]->text);


		$user2 = User::read(2);

		$this->assertEquals('foo', $user2->name);
		$this->assertEquals('foo comment first', $user2->Comment[0]->text);

	}


	public function testBelongsToRead(){
		$comment1 = Comment::read(1);
		$comment2 = Comment::read(2);
		$comment3 = Comment::read(3);

		$this->assertEquals($comment1->user_id, $comment1->User->id);	
		$this->assertEquals($comment2->user_id, $comment2->User->id);		
		$this->assertEquals($comment3->user_id, $comment3->User->id);


		$profile1 = Profile::read(1);
		$profile2 = Profile::read(2);
		$profile3 = Profile::read(3);

		$this->assertEquals($profile1->user_id, $profile1->User->id);
		$this->assertEquals($profile2->user_id, $profile2->User->id);
		$this->assertEquals($profile3->user_id, $profile3->User->id);

	}
	

	public function testBelongsToManyRead(){
		$user1 = User::read(1);
		$user2 = User::read(2);
/*
*		$conn->query("INSERT INTO users_skills(id, users_id, skills_id) 
*			VALUES(1, 1, 1), (2, 1, 2), (3, 2, 1)");
*
*		$conn->query("INSERT INTO users(id,name)
*			 VALUES(1,'bar'), (2, 'foo'), (3, 'hoge'), (4, 'fuga')");
*	
*		$conn->query("INSERT INTO skills(id, name)
*			 VALUES(1,'skill1'), (2, 'skill2'), (3, 'skill3')");
*/
		$this->assertEquals('skill1', $user1->Skill[0]->name);
		$this->assertEquals('skill2', $user1->Skill[1]->name);
		$this->assertEquals('skill1', $user2->Skill[0]->name);

		
	}


	public function testHasOneSave(){
		$user1 = User::read(1);

		$this->assertEquals(true, $user1->save());


		$user2 = User::read(3);
		$user2->Profile = Profile::create(['text'=>'create profile']);
print_r([$user2->id]);

		$this->assertEquals(true, $user2->save());
print_r([$user2->id]);
		$this->assertEquals('create profile', $user2->Profile->text);
		$this->assertEquals(3, $user2->Profile->user_id);
	}


	public function testbelongsToSave(){

/*
*	Profile BelongsTo User
*/
		$user1 = User::create(['name' => 'newUser']);

		$profile = Profile::create();
		$profile->User = $user1;
print_r($profile);
		$this->assertEquals(true, $profile->save());
		$this->assertEquals($profile->user_id, $user1->id);

/*
*	本当にセーブできたかreadで確認
*/
		$savedUser1 = User::read($profile->User->id);
	
		$this->assertEquals($profile->User->name, $savedUser1->name);
		$this->assertEquals($profile->User->id, $savedUser1->id);
		


/*
*	Comment BelongsTo User
*/
		$user2 = User::newRecord(['name' => 'newUser2']);

		$comment = Comment::create();
		$comment->User = $user2;

		$this->assertEquals(true, $comment->save());
//print_r($comment);
		$this->assertEquals($comment->user_id, $user2->id);

/*
*	本当にセーブできたかreadで確認
*/
		$savedUser2 = User::read($comment->User->id);

		$this->assertEquals($comment->User->name, $savedUser2->name);
		$this->assertEquals($comment->User->id, $savedUser2->id);

	}


	public function testHasManySave(){
		$user = User::read(3);	
		$comment1 = Comment::create(['text'=>'create']);
		$comment2 = Comment::newRecord(['text'=>'new record']);
		$user->Comment[] = $comment1;
		$user->Comment[] = $comment2;

		$this->assertEquals(true, $user->save());
		$this->assertEquals($user->id, $comment1->user_id);
		$this->assertEquals($user->id, $comment2->user_id);
		$this->assertEquals('create', $user->Comment[0]->text);
		$this->assertEquals('new record', $user->Comment[1]->text);


/*
*	本当に保存できたかreadで確認
*/
		$savedComment1 = Comment::read($user->Comment[0]->id);
		$savedComment2 = Comment::read($user->Comment[1]->id);

		$this->assertEquals('create',$savedComment1->text);
		$this->assertEquals('new record',$savedComment2->text);
		$this->assertEquals($user->id, $savedComment1->user_id);
		$this->assertEquals($user->id, $savedComment2->user_id);
		

	}


	public function testBelongsToManySave(){

/*
*	userからセーブ
*/

		$user1 = User::create(['name'=>'newUser']);
		$skill1 = Skill::create(['name'=>'newSkill']);
		$user1->Skill[] = $skill1;


		$this->assertEquals(true, $user1->save());

/*
*	本当に保存できたかreadで確認
*/
		$savedUser1Skill = Skill::read($user1->Skill[0]->id);

		$this->assertEquals('newSkill', $savedUser1Skill->name);

		$savedSkill1User = User::read($user1->id);
		
		$this->assertEquals('newUser', $savedSkill1User->name);



/*
*	skillからセーブ
*/

		$skill2 = Skill::create(['name'=>'newSkill']);
		$user2 = User::create(['name'=>'newUser']);
		$skill2->User[] = $user2;

		$this->assertEquals(true, $skill2->save());
	
/*
*	本当に保存できたかreadで確認
*/
		$savedSkill2User = User::read($skill2->User[0]->id);
		
		$this->assertEquals('newUser', $savedSkill2User->name);

		$savedUser2Skill = Skill::read($skill2->id);

		$this->assertEquals('newSkill', $savedUser1Skill->name);


	}


	public function testFindAll(){
/*
		$conn->query("DELETE FROM users");
		$conn->query("INSERT INTO users(id,name) VALUES(1,'bar'), (2, 'foo'), (3, 'hoge'), (4, 'fuga')");
		
		$conn->query("DELETE FROM comments");
		$conn->query("INSERT INTO comments(id, text, user_id)
			VALUES(1, 'bar comment first', 1), (2, 'bar comment second', 1), (3, 'foo comment first', 2)");

		$conn->query("DELETE FROM users_skills");
		$conn->query("INSERT INTO users_skills(id, users_id, skills_id) 
			VALUES(1, 1, 1), (2, 1, 2), (3, 2, 1)");
	
		$conn->query("DELETE FROM skills");
		$conn->query("INSERT INTO skills(id, name)
			 VALUES(1,'skill1'), (2, 'skill2'), (3, 'skill3')");

		$conn->query("DELETE FROM profiles");
		$conn->query("INSERT INTO profiles(id, text, user_id) 
			VALUES(1, 'bar profile', 1), (2, 'foo frofile', 2), (3, 'hoge frofile', 3)");
*/

		$users = User::findAll();

		$this->assertEquals('bar profile', $users[0]->Profile->text);
		$this->assertEquals('foo frofile', $users[1]->Profile->text);
		$this->assertEquals('hoge frofile', $users[2]->Profile->text);
		$this->assertEquals(null, $users[3]->Profile);

		$this->assertEquals('bar comment first', $users[0]->Comment[0]->text);
		$this->assertEquals('bar comment second', $users[0]->Comment[1]->text);
		$this->assertEquals('foo comment first', $users[1]->Comment[0]->text);
		$this->assertEquals([], $users[2]->Comment);
		$this->assertEquals([], $users[3]->Comment);
	
		$this->assertEquals('skill1', $users[0]->Skill[0]->name);
		$this->assertEquals('skill1', $users[1]->Skill[0]->name);
		$this->assertEquals([], $users[2]->Skill);
		$this->assertEquals([], $users[3]->Skill);

	}


	public function testLimit(){
/*
		$conn->query("DELETE FROM users");
		$conn->query("INSERT INTO users(id,name) VALUES(1,'bar'), (2, 'foo'), (3, 'hoge'), (4, 'fuga')");
		
		$conn->query("DELETE FROM comments");
		$conn->query("INSERT INTO comments(id, text, user_id)
			VALUES(1, 'bar comment first', 1), (2, 'bar comment second', 1), (3, 'foo comment first', 2)");

		$conn->query("DELETE FROM users_skills");
		$conn->query("INSERT INTO users_skills(id, users_id, skills_id) 
			VALUES(1, 1, 1), (2, 1, 2), (3, 2, 1)");
	
		$conn->query("DELETE FROM skills");
		$conn->query("INSERT INTO skills(id, name)
			 VALUES(1,'skill1'), (2, 'skill2'), (3, 'skill3')");

		$conn->query("DELETE FROM profiles");
		$conn->query("INSERT INTO profiles(id, text, user_id) 
			VALUES(1, 'bar profile', 1), (2, 'foo frofile', 2), (3, 'hoge frofile', 3)");
*/

		$users = User::limit(4);

		$this->assertEquals('bar profile', $users[0]->Profile->text);
		$this->assertEquals('foo frofile', $users[1]->Profile->text);
		$this->assertEquals('hoge frofile', $users[2]->Profile->text);
		$this->assertEquals(null, $users[3]->Profile);

		$this->assertEquals('bar comment first', $users[0]->Comment[0]->text);
		$this->assertEquals('bar comment second', $users[0]->Comment[1]->text);
		$this->assertEquals('foo comment first', $users[1]->Comment[0]->text);
		$this->assertEquals([], $users[2]->Comment);
		$this->assertEquals([], $users[3]->Comment);
	
		$this->assertEquals('skill1', $users[0]->Skill[0]->name);
		$this->assertEquals('skill1', $users[1]->Skill[0]->name);
		$this->assertEquals([], $users[2]->Skill);
		$this->assertEquals([], $users[3]->Skill);
	}



	public function testFindBy(){

/*
		$conn->query("DELETE FROM users");
		$conn->query("INSERT INTO users(id,name) VALUES(1,'bar'), (2, 'foo'), (3, 'hoge'), (4, 'fuga')");
		
		$conn->query("DELETE FROM comments");
		$conn->query("INSERT INTO comments(id, text, user_id)
			VALUES(1, 'bar comment first', 1), (2, 'bar comment second', 1), (3, 'foo comment first', 2)");

		$conn->query("DELETE FROM users_skills");
		$conn->query("INSERT INTO users_skills(id, users_id, skills_id) 
			VALUES(1, 1, 1), (2, 1, 2), (3, 2, 1)");
	
		$conn->query("DELETE FROM skills");
		$conn->query("INSERT INTO skills(id, name)
			 VALUES(1,'skill1'), (2, 'skill2'), (3, 'skill3')");

		$conn->query("DELETE FROM profiles");
		$conn->query("INSERT INTO profiles(id, text, user_id) 
			VALUES(1, 'bar profile', 1), (2, 'foo frofile', 2), (3, 'hoge frofile', 3)");
*/

		$users = User::findBy('name', '=', 'bar');

		$this->assertEquals('bar profile', $users[0]->Profile->text);

		$this->assertEquals('bar comment first', $users[0]->Comment[0]->text);
		$this->assertEquals('bar comment second', $users[0]->Comment[1]->text);
	
		$this->assertEquals('skill1', $users[0]->Skill[0]->name);
		$this->assertEquals('skill2', $users[0]->Skill[1]->name);
		

	}



}













