<?php

namespace W7\Tests;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Console\Input\ArgvInput;
use W7\App;
use W7\Console\Application;
use W7\Contract\Translation\LoaderInterface;
use W7\Core\Exception\ValidatorException;
use W7\Facade\Container;
use W7\Facade\Output;
use W7\Facade\Validator;
use W7\Lang\Loader\FileLoader;

class UserRule implements Rule {
	private $error;

	public function passes($attribute, $value) {
		if (!is_string($value)) {
			$this->error = '用户名类型错误';
			return false;
		}
		if (strlen($value) < 6) {
			$this->error = '用户名不能少于6位';
			return false;
		}
		if (strlen($value) > 10) {
			$this->error = '用户名不能多余10位';
			return false;
		}

		return true;
	}

	public function message() {
		return $this->error;
	}
}

class ValidateTest extends TestCase {
	public function setUp(): void {
		parent::setUp();
		App::getApp()->getContainer()->set(LoaderInterface::class, function () {
			$paths = [
				__DIR__ . '/../vendor/laravel-lang/lang/src',
				BASE_PATH . '/lang'
			];

			$loader = new FileLoader(new Filesystem(), BASE_PATH, $paths);
			if (\is_callable([$loader, 'addJsonPath'])) {
				$loader->addJsonPath(BASE_PATH . '/vendor/laravel-lang/lang/json/');
				$loader->addJsonPath(BASE_PATH . '/lang/json/');
			}

			return $loader;
		});
	}

	private function validate(array $data, array $rules, array $messages = [], array $customAttributes = []) {
		try {
			/**
			 * @var Factory $validate
			 */
			$result = Validator::make($data, $rules, $messages, $customAttributes)
				->validate();
		} catch (ValidationException $e) {
			$errorMessage = [];
			$errors = $e->errors();
			foreach ($errors as $field => $message) {
				$errorMessage[] = $message[0];
			}
			throw new ValidatorException(implode('; ', $errorMessage), 403);
		}

		return $result;
	}

	public function testMake() {
		/**
		 * @var Application $application
		 */
		$application = Container::get(Application::class);
		$command = $application->get('make:validate');

		$command->run(new ArgvInput([
			'input',
			'--name=test'
		]), Output::getFacadeRoot());

		$file = APP_PATH . '/Model/Validate/TestRule.php';

		$this->assertFileExists($file);

		unlink($file);
		rmdir(APP_PATH . '/Model/Validate');
	}

	public function testValidate() {
		$data = [
			'key' => 1,
			'value' => 2
		];

		$result = $this->validate($data, [
			'key' => 'required',
			'value' => 'required'
		]);

		$this->assertSame(1, $result['key']);
		$this->assertSame(2, $result['value']);

		try {
			$this->validate($data, [
				'key' => 'required',
				'value' => 'required',
				'test' => 'required'
			]);
		} catch (ValidatorException $e) {
			$this->assertSame(403, $e->getCode());
			$this->assertSame('{"error":"test 不能为空。"}', $e->getMessage());
		}
	}

	public function testMessage() {
		$data = [
			'key' => 1,
			'value' => 2
		];
		try {
			$this->validate($data, [
				'key' => 'required',
				'value' => 'required',
				'test' => 'required'
			], [
				'test.required' => 'test参数错误'
			]);
		} catch (ValidatorException $e) {
			$this->assertSame(403, $e->getCode());
			$this->assertSame('{"error":"test参数错误"}', $e->getMessage());
		}
	}

	public function testExtend() {
		if (!file_exists(BASE_PATH . '/lang/zh_CN')) {
			mkdir(BASE_PATH . '/lang/zh_CN', 0777, true);
		}

		copy(__DIR__ . '/tmp/lang/zh_CN/validation.php', BASE_PATH . '/lang/zh_CN/validation.php');

		Validator::extend('user_validate', function ($attribute, $value, $parameters) {
			return $value === 'test';
		});

		$data = [
			'key' => 1,
			'value' => 'test'
		];

		$result = $this->validate($data, [
			'key' => 'required',
			'value' => 'user_validate'
		], [
			'test.required' => 'test参数错误'
		]);
		$this->assertSame(1, $result['key']);
		$this->assertSame('test', $result['value']);

		$data = [
			'key' => 1,
			'value' => 'test1'
		];
		try {
			$this->validate($data, [
				'key' => 'required',
				'value' => 'user_validate'
			], [
				'test.required' => 'test参数错误'
			]);
		} catch (ValidatorException $e) {
			$this->assertSame(403, $e->getCode());
			$this->assertSame('{"error":"自定义验证"}', $e->getMessage());
		}

		unlink(BASE_PATH . '/lang/zh_CN/validation.php');
	}

	public function testUserRule() {
		$data = [
			'name' => '1'
		];

		try{
			$this->validate($data, [
				'name' => [new UserRule()]
			]);
		} catch (\Throwable $e) {
			$this->assertSame('{"error":"用户名不能少于6位"}', $e->getMessage());
		}

		$data = [
			'name' => '12121212111'
		];

		try{
			$this->validate($data, [
				'name' => [new UserRule()]
			]);
		} catch (\Throwable $e) {
			$this->assertSame('{"error":"用户名不能多余10位"}', $e->getMessage());
		}

		$data = [
			'name' => '12121211'
		];

		$result = $this->validate($data, [
			'name' => [new UserRule()]
		]);
		$this->assertSame('12121211', $result['name']);
	}
}