<?php

namespace Mickadoo\Application\ServiceProvider;

use DerAlex\Silex\YamlConfigServiceProvider;
use Silex\Application;
use Symfony\Component\Yaml\Yaml;

class EnvOverrideYamlConfigServiceProvider extends YamlConfigServiceProvider
{
    public function register(Application $app)
    {
        $file = $this->file;
        if (!file_exists($file)) {
            $file = $this->file . '.dist';
        }

        $config = Yaml::parse(file_get_contents($file));

        if (is_array($config)) {
            $config = $this->replaceWithEnvVariables('mailer', $config);
            $app['config'] = $config;
        }
    }

    private function replaceWithEnvVariables($context, &$config)
    {
        foreach ($config as $key => &$value) {
            if (is_array($value)) {
                $this->replaceWithEnvVariables($context . '__' . $key, $value);
            } else {
                $envVariable = getenv(strtoupper($context . '__' . $key));
                if ($envVariable) {
                    $value = $envVariable;
                }
            }
        }
    }
}