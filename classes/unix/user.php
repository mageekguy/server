<?php

namespace server\unix;

class user
{
	protected $login = null;
	protected $uid = null;
	protected $gid = null;
	protected $home = null;

	public function __construct($login = null)
	{
		$this->setHome();

		if ($login !== null)
		{
			$this->setLogin($login);
		}
	}

	public function __toString()
	{
		return (string) $this->login;
	}

	public function getUid()
	{
		return $this->uid ?: null;
	}

	public function getGid()
	{
		return $this->gid ?: null;
	}

	public function setHome(user\home $home = null)
	{
		$this->home = $home ?: new user\home();

		return $this;
	}

	public function getHome()
	{
		return $this->home;
	}

	public function setHomePath($path)
	{
		$this->home->setPath($path);

		return $this;
	}

	public function getHomePath()
	{
		return ($this->uid === null ? null : (string) $this->home);
	}

	public function setLogin($login)
	{
		$userData = posix_getpwnam($login);

		if ($userData === false)
		{
			throw $this->getException('User \'' . $login . '\' does not exist');
		}

		$this->login = $login;
		$this->uid = $userData['uid'];
		$this->gid = $userData['gid'];
		$this->home->setPath($userData['dir']);

		return $this;
	}

	public function goToHome()
	{
		try
		{
			$this->home->go();
		}
		catch (\exception $exception)
		{
			throw $this->getException($exception->getMessage());
		}

		return $this;
	}

	protected function getException($message)
	{
		return new user\exception($message);
	}
}
