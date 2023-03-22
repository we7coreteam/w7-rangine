<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Tests\Model;

use Illuminate\Database\Schema\Blueprint;
use W7\Core\Database\ModelAbstract;
use W7\Core\Event\Dispatcher;
use W7\Facade\DB;
use W7\Facade\Event;
use W7\Core\Listener\ListenerAbstract;

class User extends ModelAbstract {
	protected $table = 'user';
	protected $dispatchesEvents = [
		'saved' => SavedEvent::class
	];
}

class SavedEvent {
	private $user;

	public function __construct(User $user) {
		$this->user = $user;
	}

	public function check() {
		$this->user->saved = true;
	}
}

class SavedListener extends ListenerAbstract {
	public function run(...$params) {
		/**
		 * @var SavedEvent $savedEvent
		 */
		$savedEvent = $params[0];
		$savedEvent->check();
	}
}

class EventTest extends ModelTestAbstract {
	public function testEvent() {
		/**
		 * @var Dispatcher $event
		 */
		$event = Event::getFacadeRoot();
		$event->listen(SavedEvent::class, SavedListener::class);
		DB::connection()->getSchemaBuilder()->dropIfExists('user');
		DB::connection()->getSchemaBuilder()->create('user', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->timestamps();
			$table->string('name');
		});

		$model = new User();
		$model->id = 1;
		$model->name = 'test';
		$model->save();

		$value = (new User())->where('id', '=', 1)->first();
		$this->assertSame(1, $value->id);
		$this->assertSame('test', $value->name);
		$this->assertSame(true, $model->saved);

		DB::connection()->getSchemaBuilder()->dropIfExists('user');
	}
}
