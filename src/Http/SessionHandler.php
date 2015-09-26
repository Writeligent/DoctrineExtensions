<?php

namespace Writeligent\DoctrineExtensions\Http;

use Nette;

class SessionHandler implements \SessionHandlerInterface
{

	/** @var \Doctrine\DBAL\Connection */
	protected $connection;

	/** @var string */
	protected $tableName;

	/** @var string */
	protected $lockId;

	public function __construct(\Doctrine\DBAL\Connection $connection , $tableName) {
		$this->lockId = uniqid();
		$this->connection = $connection;
		$this->tableName = $tableName;
	}

	public function open($savePath, $name) {
	}

	public function read($id) {
		return $this->lock($id)['data'];
	}

	public function write($id, $data) {
		$result = $this->lock($id, $data);

		if ($result['data'] !== $data) {
			$this->update($id, ['data' => $data]);
		}

		return true;
	}

	public function destroy($id) {
		$this->lock($id);
		$this->connection->delete($this->tableName, ['name' => $id]);
	}

	public function gc($maxlifetime) {
		$qb = $this->connection->createQueryBuilder();
		$qb->delete($this->tableName)
			->where('time < :end');
		$this->connection->executeQuery($qb, ['end' => new \DateTime(sprintf('- %d seconds', $maxlifetime))], ['end' => 'datetime']);
	}

	public function close() {
		$this->connection->update($this->tableName, ['locked' => null], ['locked' => $this->lockId]);
	}

	protected function lock($id, $data = '') {

		while (($result = $this->fetch($id)) === false) {
			usleep(100000);
		}

		if ($result === null) {
			$result = [
				'name' => $id,
				'data' => $data,
				'time' => new \DateTime(),
				'locked' => $this->lockId,
			];
			$this->connection->insert($this->tableName, $result, [
				\PDO::PARAM_STR,
				\PDO::PARAM_STR,
				'datetime',
				\PDO::PARAM_STR,
			]);
		} else {
			$this->update($id, ['locked' =>$this->lockId]);
		}
		return $result;
	}

	protected function fetch($id) {
		$qb = $this->connection->createQueryBuilder();
		$qb->select('*')
			->from($this->tableName)
			->where('name = :name');
		$result = $this->connection->fetchAssoc($qb, ['name' => $id]);

		if ($result === false) {
			return null;
		}
		if ($result['locked'] !== null && $result['locked'] !== $this->lockId) {
			return false;
		}
		return $result;
	}

	protected function update($id, $data) {
		$this->connection->update($this->tableName, $data, ['name' => $id]);
	}

}
