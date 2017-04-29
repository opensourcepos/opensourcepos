<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Caching\Storages;

use Nette;
use Nette\Caching\Cache;


/**
 * SQLite based journal.
 */
class SQLiteJournal implements IJournal
{
	use Nette\SmartObject;

	/** @string */
	private $path;

	/** @var \PDO */
	private $pdo;


	/**
	 * @param  string
	 */
	public function __construct($path)
	{
		$this->path = $path;
	}


	private function open()
	{
		if (!extension_loaded('pdo_sqlite')) {
			throw new Nette\NotSupportedException('SQLiteJournal requires PHP extension pdo_sqlite which is not loaded.');
		}

		if ($this->path !== ':memory:' && !is_file($this->path)) {
			touch($this->path); // ensures ordinary file permissions
		}

		$this->pdo = new \PDO('sqlite:' . $this->path);
		$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->pdo->exec('
			PRAGMA foreign_keys = OFF;
			PRAGMA journal_mode = WAL;
			CREATE TABLE IF NOT EXISTS tags (
				key BLOB NOT NULL,
				tag BLOB NOT NULL
			);
			CREATE TABLE IF NOT EXISTS priorities (
				key BLOB NOT NULL,
				priority INT NOT NULL
			);
			CREATE INDEX IF NOT EXISTS idx_tags_tag ON tags(tag);
			CREATE UNIQUE INDEX IF NOT EXISTS idx_tags_key_tag ON tags(key, tag);
			CREATE UNIQUE INDEX IF NOT EXISTS idx_priorities_key ON priorities(key);
			CREATE INDEX IF NOT EXISTS idx_priorities_priority ON priorities(priority);
		');
	}


	/**
	 * Writes entry information into the journal.
	 * @param  string
	 * @param  array
	 * @return void
	 */
	public function write($key, array $dependencies)
	{
		if (!$this->pdo) {
			$this->open();
		}
		$this->pdo->exec('BEGIN');

		if (!empty($dependencies[Cache::TAGS])) {
			$this->pdo->prepare('DELETE FROM tags WHERE key = ?')->execute([$key]);

			foreach ((array) $dependencies[Cache::TAGS] as $tag) {
				$arr[] = $key;
				$arr[] = $tag;
			}
			$this->pdo->prepare('INSERT INTO tags (key, tag) SELECT ?, ?' . str_repeat('UNION SELECT ?, ?', count($arr) / 2 - 1))
				->execute($arr);
		}

		if (!empty($dependencies[Cache::PRIORITY])) {
			$this->pdo->prepare('REPLACE INTO priorities (key, priority) VALUES (?, ?)')
				->execute([$key, (int) $dependencies[Cache::PRIORITY]]);
		}

		$this->pdo->exec('COMMIT');
	}


	/**
	 * Cleans entries from journal.
	 * @param  array
	 * @return array|NULL  removed items or NULL when performing a full cleanup
	 */
	public function clean(array $conditions)
	{
		if (!$this->pdo) {
			$this->open();
		}
		if (!empty($conditions[Cache::ALL])) {
			$this->pdo->exec('
				BEGIN;
				DELETE FROM tags;
				DELETE FROM priorities;
				COMMIT;
			');

			return NULL;
		}

		$unions = $args = [];
		if (!empty($conditions[Cache::TAGS])) {
			$tags = (array) $conditions[Cache::TAGS];
			$unions[] = 'SELECT DISTINCT key FROM tags WHERE tag IN (?' . str_repeat(', ?', count($tags) - 1) . ')';
			$args = $tags;
		}

		if (!empty($conditions[Cache::PRIORITY])) {
			$unions[] = 'SELECT DISTINCT key FROM priorities WHERE priority <= ?';
			$args[] = (int) $conditions[Cache::PRIORITY];
		}

		if (empty($unions)) {
			return [];
		}

		$unionSql = implode(' UNION ', $unions);

		$this->pdo->exec('BEGIN IMMEDIATE');

		$stmt = $this->pdo->prepare($unionSql);
		$stmt->execute($args);
		$keys = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);

		if (empty($keys)) {
			$this->pdo->exec('COMMIT');
			return [];
		}

		$this->pdo->prepare("DELETE FROM tags WHERE key IN ($unionSql)")->execute($args);
		$this->pdo->prepare("DELETE FROM priorities WHERE key IN ($unionSql)")->execute($args);
		$this->pdo->exec('COMMIT');

		return $keys;
	}

}
