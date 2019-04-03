<?php
namespace Amos\MailRobot;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Contracts\Events\Dispatcher;
class MailRobotServiceProvider extends ServiceProvider
{
    /**
     *
     */
    public function register()
    {
        $this->app->singleton('mailRobot', function () {
            return new MailRobot();
        });

        if ($this->app->runningInConsole()) {
            $this->registerConsoleCommands();
        }
    }

    /**
     * Bootstrap the application services.
     *
     * @param \Illuminate\Routing\Router $router
     */
//    public function boot(Router $router, Dispatcher $event)
//    {
//        $this->loadMigrationsFrom(realpath(__DIR__.'/../migrations'));
//    }

    /**
     * Register the commands accessible from the Console.
     */
    private function registerConsoleCommands()
    {
        $this->commands(Commands\EmailRobot::class);
    }
}