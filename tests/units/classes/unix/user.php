<?php

namespace server\tests\units\unix;

require __DIR__ . '/../../runner.php';

use
	atoum,
	server\unix,
	server\unix\user as testedClass
;

class user extends atoum
{
	public function test__construct()
	{
		$this
			->if($user = new testedClass())
			->then
				->castToString($user)->isEmpty()
				->variable($user->getUid())->isNull()
				->variable($user->getGid())->isNull()
				->object($user->getHome())->isEqualTo(new unix\user\home())
				->variable($user->getHomePath())->isNull()

			->if($this->function->posix_getpwnam = false)
			->then
				->exception(function() use (& $login) { new testedClass($login = uniqid()); })
					->isInstanceOf('server\unix\user\exception')
					->hasMessage('User \'' . $login . '\' does not exist')

			->if(
				$this->function->posix_getpwnam = array('uid' => $uid = rand(1, PHP_INT_MAX), 'gid' => $gid = rand(1, PHP_INT_MAX), 'dir' => $dir = uniqid()),
				$user = new testedClass($login = uniqid())
			)
			->then
				->castToString($user)->isEqualTo($login)
				->integer($user->getUid())->isEqualTo($uid)
				->integer($user->getGid())->isEqualTo($gid)
				->object($user->getHome())->isEqualTo(new unix\user\home($dir))
				->string($user->getHomePath())->isEqualTo($dir)
		;
	}

	public function testSetHome()
	{
		$this
			->if($user = new testedClass())
			->then
				->object($user->setHome($home = new unix\user\home()))->isIdenticalTo($user)
				->object($user->getHome())->isIdenticalTo($home)
				->object($user->setHome())->isIdenticalTo($user)
				->object($user->getHome())
					->isNotIdenticalTo($home)
					->isEqualTo(new unix\user\home())
		;
	}

	public function testSetHomePath()
	{
		$this
			->given(
				$user = new testedClass(),
				$user->setHome($home = new \mock\server\unix\user\home())
			)
			->then
				->object($user->setHomePath($path = uniqid()))->isIdenticalTo($user)
				->mock($home)->call('setPath')->withArguments($path)->once()
		;
	}

	public function testSetLogin()
	{
		$this
			->given($user = new testedClass())

			->if($this->function->posix_getpwnam = false)
			->then
				->exception(function() use ($user, & $login) { $user->setLogin($login = uniqid()); })
					->isInstanceOf('server\unix\user\exception')
					->hasMessage('User \'' . $login . '\' does not exist')

			->if(
				$this->function->posix_getpwnam = array('uid' => $uid = rand(1, PHP_INT_MAX), 'gid' => $gid = rand(1, PHP_INT_MAX), 'dir' => $dir = uniqid())
			)
			->then
				->object($user->setLogin($login = uniqid()))->isIdenticalTo($user)
				->castToString($user)->isEqualTo($login)
				->integer($user->getUid())->isEqualTo($uid)
				->integer($user->getGid())->isEqualTo($gid)
				->string($user->getHomePath())->isEqualTo($dir)

			->if(
				$user->setHomePath(uniqid()),
				$this->function->posix_getpwnam = array('uid' => $otherUid = rand(1, PHP_INT_MAX), 'gid' => $otherGid = rand(1, PHP_INT_MAX), 'dir' => $otherDir = uniqid())
			)
			->then
				->object($user->setLogin($otherLogin = uniqid()))->isIdenticalTo($user)
				->castToString($user)->isEqualTo($otherLogin)
				->integer($user->getUid())->isEqualTo($otherUid)
				->integer($user->getGid())->isEqualTo($otherGid)
				->string($user->getHomePath())->isEqualTo($otherDir)
		;
	}

	public function testGoToHome()
	{
		$this
			->given(
				$user = new testedClass(),
				$user->setHome($home = new \mock\server\unix\user\home())
			)

			->if($this->calling($home)->go->returnThis())
			->then
				->object($user->goToHome())->isEqualTo($user)
				->mock($home)->call('go')->once()

			->if($this->calling($home)->go->throw = $exception = new \exception())
			->then
				->exception(function() use ($user) { $user->goToHome(); })
					->isInstanceOf('server\unix\user\exception')
					->hasMessage($exception->getMessage())
		;
	}
}
