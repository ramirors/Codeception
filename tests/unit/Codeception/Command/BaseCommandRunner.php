<?php
use Codeception\Util\Stub;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class BaseCommandRunner extends \PHPUnit_Framework_TestCase {

    /**
     * @var \Codeception\Command\Base
     */
    protected $command;

    public $filename = "";
    public $content = "";
    public $output = "";
    public $config = array();
    public $log = array();
    
    protected $commandName = 'do:stuff';

    protected function execute($args = array())
    {
        $app = new Application();
        $app->add($this->command);

        $default = \Codeception\Configuration::$defaultConfig;
        $default['paths']['tests'] = __DIR__;
        $conf = \Codeception\Configuration::suiteSettings('unit', $default);
        $this->config = array_merge($conf, $this->config);
        
        $commandTester = new CommandTester($app->find($this->commandName));
        $args['command'] = $this->commandName;
        $commandTester->execute($args, array('interactive' => false));
        $this->output = $commandTester->getDisplay();
    }
    
    protected function makeCommand($className)
    {
        $this->config = array();
        $self = $this;
        $this->command = Stub::construct($className, array($this->commandName), array(
            'save' => function($file, $output) use ($self) {
                $self->filename = $file;
                $self->content = $output;
                $self->log[] = array('filename' => $file, 'content' => $output);
                return true;
            },
            'getSuiteConfig' => function() use ($self) {
                return $self->config;
            },
            'buildPath' => function($path) {
                return $path;
            },
            'getSuites' => function() {
                return array('shire');
            },
            'getApplication' => function() {
                return new \Codeception\Maybe;
            }
        ));
    }

    protected function isValidPhp()
    {
        return eval(substr($this->content,6)) === null;
    }


}
