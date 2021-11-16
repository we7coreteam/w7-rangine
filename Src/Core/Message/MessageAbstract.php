<?php
/**
 * @author donknap
 * @date 18-11-24 下午9:40
 */

namespace W7\Core\Message;

/**
 * Class MessageAbstract
 * @package W7\Core\Message
 *
 * @property  $id
 */
abstract class MessageAbstract {
	public static array $createMessageCallbacks = [];
	public static array $propertyMapping = [];

	public string $messageType;

	public function getId() {
		return $this->id;
	}

	public static function createMessageUsing(callable $callback): void {
		if (is_null($callback)) {
			static::$createMessageCallbacks = [];
		} else {
			static::$createMessageCallbacks[] = $callback;
		}
	}

	public function pack(): string {
		$classname = static::class;

		if (empty(self::$propertyMapping[$classname])) {
			$reflection = new \ReflectionClass($classname);
			$default = $reflection->getDefaultProperties();

			if ($reflection->getProperties()) {
				foreach ($reflection->getProperties() as $row) {
					if (!$row->isStatic()) {
						self::$propertyMapping[$classname][$row->getName()] = $default[$row->getName()];
					}
				}
			}
		}
		$property = self::$propertyMapping[$classname];

		$data = [
			'class' => static::class
		];
		foreach ($property as $name => $defaultValue) {
			if ($this->$name !== $defaultValue) {
				$data[$name] = $this->$name;
			} else {
				$data[$name] = $defaultValue;
			}
		}

		if (!empty(static::$createMessageCallbacks)) {
			foreach (static::$createMessageCallbacks as $callback) {
				$data = array_merge($data, call_user_func(
					$callback, $this, $data
				));
			}
		}

		return serialize($data);
	}
}