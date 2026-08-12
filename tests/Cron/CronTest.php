<?php

namespace Stackstra\Tests\Cron;

use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionProperty;
use Stackstra\Cron\Cron;
use Stackstra\Tests\TestCase;

#[CoversClass(Cron::class)]
class CronTest extends TestCase
{
    public function testConstruct(): void
    {
        // no argument: no tasks registered
        $cron = new Cron();
        $this->assertSame([], $cron->tasks());

        // tasks passed to the constructor are registered via add_tasks()
        $cron = new Cron(['a' => 10]);
        $this->assertTrue($cron->is_task_should_be_launched('a'));
    }

    public function testAddTask(): void
    {
        $cron = new Cron();
        $cron->addTask('a', 10);

        // a task never seen before is always launchable
        $this->assertTrue($cron->is_task_should_be_launched('a'));
    }

    public function testAddTasks(): void
    {
        $cron = new Cron();
        $cron->add_tasks(['a' => 1, 'b' => 2]);

        $this->assertTrue($cron->is_task_should_be_launched('a'));
        $this->assertTrue($cron->is_task_should_be_launched('b'));
    }

    public function testSetSleep(): void
    {
        $cron = new Cron();

        // fractional value is cast to int
        $cron->set_sleep(5.7);
        $this->assertSame(5, $this->sleepProperty($cron)->getValue($cron));
    }

    public function testSleep(): void
    {
        // sleep_last is null (never slept before): $sleep defaults to 0, so usleep(0) runs immediately
        $cron = new Cron();
        $cron->sleep();
        $this->assertIsFloat($this->sleepLastProperty($cron)->getValue($cron));

        // elapsed time comfortably exceeds $sleep: the computed $sleep goes negative and is clamped to 0
        // (sleep_last/now are injected via reflection so the test doesn't depend on real elapsed time)
        $cron = new Cron();
        $this->sleepProperty($cron)->setValue($cron, 0);
        $this->sleepLastProperty($cron)->setValue($cron, microtime(true) - 10);

        $cron->sleep();
        $this->assertIsFloat($this->sleepLastProperty($cron)->getValue($cron));

        // computed $sleep exceeds the configured max: it's clamped down to $this->sleep instead
        // (sleep_last is pushed into the future, keep the clamped value tiny so usleep() stays fast)
        $cron = new Cron();
        $this->sleepProperty($cron)->setValue($cron, 0.0005);
        $this->sleepLastProperty($cron)->setValue($cron, microtime(true) + 1000);

        $cron->sleep();
        $this->assertIsFloat($this->sleepLastProperty($cron)->getValue($cron));

        // computed $sleep is within (0, $this->sleep]: neither branch triggers, it sleeps that amount as-is
        $cron = new Cron();
        $this->sleepProperty($cron)->setValue($cron, 0.01);
        $this->sleepLastProperty($cron)->setValue($cron, microtime(true) - 0.005);

        $cron->sleep();
        $this->assertIsFloat($this->sleepLastProperty($cron)->getValue($cron));
    }

    public function testTasks(): void
    {
        $cron = new Cron(['a' => 0, 'b' => 0]);

        // neither task has run before: both are due
        $this->assertSame(['a', 'b'], $cron->tasks());

        // interval 0: a task is due again immediately after finishing
        $cron->task_finished('a');
        $this->assertSame(['a', 'b'], $cron->tasks());

        // a long interval keeps a just-finished task off the due list
        $cron2 = new Cron(['a' => 1000000]);
        $cron2->task_finished('a');
        $this->assertSame([], $cron2->tasks());
    }

    public function testIsTaskShouldBeLaunched(): void
    {
        $cron = new Cron(['a' => 10]);

        // never run before: due regardless of timestamp
        $this->assertTrue($cron->is_task_should_be_launched('a'));

        $cron->task_finished('a', 100.0);

        // timestamp before history + interval: not due
        $this->assertFalse($cron->is_task_should_be_launched('a', 109.0));

        // timestamp exactly at history + interval: due (isGreaterOrEqual)
        $this->assertTrue($cron->is_task_should_be_launched('a', 110.0));

        // timestamp after: due
        $this->assertTrue($cron->is_task_should_be_launched('a', 111.0));

        // timestamp omitted: falls back to the current time
        $cron->task_finished('a');
        $this->assertFalse($cron->is_task_should_be_launched('a'));
    }

    public function testTaskFinished(): void
    {
        $cron = new Cron(['a' => 10]);

        // explicit timestamp
        $cron->task_finished('a', 100.0);
        $this->assertFalse($cron->is_task_should_be_launched('a', 105.0));

        // timestamp omitted: defaults to now
        $before = microtime(true);
        $cron->task_finished('a');
        $this->assertFalse($cron->is_task_should_be_launched('a', $before));
    }

    public function testTimestamp(): void
    {
        $cron = new Cron();

        $before = microtime(true);
        $timestamp = $cron->timestamp();
        $after = microtime(true);

        $this->assertIsFloat($timestamp);
        $this->assertGreaterThanOrEqual($before, $timestamp);
        $this->assertLessThanOrEqual($after, $timestamp);
    }

    private function sleepProperty(Cron $cron): ReflectionProperty
    {
        return new ReflectionProperty($cron, 'sleep');
    }

    private function sleepLastProperty(Cron $cron): ReflectionProperty
    {
        return new ReflectionProperty($cron, 'sleep_last');
    }
}
