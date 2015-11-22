<?php

namespace Mickadoo\Application;

use DerAlex\Silex\YamlConfigServiceProvider;
use Mickadoo\Mailer\Exception\MailerException;
use Mickadoo\Mailer\Service\ArrayHelper;
use Mickadoo\Mailer\Service\MailContentGenerator;
use Mickadoo\Mailer\Service\MailPlaceholderChecker;
use Mickadoo\Mailer\SwiftMailer;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Silex\Application as BaseApplication;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Translator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Loader\YamlFileLoader;

class Application extends BaseApplication
{
    public function setUp()
    {
        $this->registerConfig();
        $this->registerMailer();
        $this->registerLogger();
        $this->registerTranslator();
        $this->registerTwig();
        $this->registerCustomServices();
        $this->enableJsonContentParsing();
        $this->registerErrorHandler();
    }

    private function registerErrorHandler()
    {
        $this->error(function (\Exception $exception) {

            $message = $exception->getMessage();
            $code = 500;

            switch (get_class($exception)) {
                case \Twig_Error_Loader::class:
                    $message = "I don't know about any message with that type (-_-)ゞ";
                    $code = 400;
                    break;
                case \Swift_RfcComplianceException::class:
                    $message = "✉ That doesn't look like a real e-mail ✉";
                    $code = 400;
            }

            return new Response(
                json_encode(['error' => $message]),
                $code,
                ['Content-Type' => 'application/json']
            );
        });
    }

    private function registerMailer()
    {
        $smtpConfig = $this['config']['smtp'];

        $transport = \Swift_SmtpTransport::newInstance(
            $smtpConfig['host'], $smtpConfig['port'], $smtpConfig['security']
        );

        $transport
            ->setUsername($smtpConfig['username'])
            ->setPassword($smtpConfig['password']);

        $mailer = \Swift_Mailer::newInstance($transport);
        $this['mailer'] = $mailer;
    }

    private function registerLogger()
    {
        $errorLogPath = $this['root_directory'] . '/app/logs/error.log';
        $mailLogPath = $this['root_directory'] . '/app/logs/mail.log';

        $this->register(new MonologServiceProvider(), array(
            'monolog.logfile' => $errorLogPath,
            'monolog.level' => Logger::ERROR,
            'monolog.bubble' => false,
        ));

        $this['mail.logger'] = $this->share(function ($this) use ($mailLogPath, $errorLogPath) {
            /** @var Logger $log */
            $log = new $this['monolog.logger.class']('mailer');
            $successHandler = new StreamHandler($mailLogPath, Logger::INFO, false);
            $errorHandler = new StreamHandler($errorLogPath, Logger::ERROR, false);
            $log->pushHandler($successHandler);
            $log->pushHandler($errorHandler);

            return $log;
        });
    }

    private function registerTwig()
    {
        $twigTemplatePath = $this['root_directory'] . $this['config']['twig']['template_directory'];

        $this->register(new TwigServiceProvider(), array(
            'twig.path' => $twigTemplatePath,
            'twig.options' => $this['config']['twig'],
        ));

        if ($this['config']['twig']['debug']) {
            /** @var \Twig_Environment $twig */
            $twig = $this['twig'];
            $twig->addExtension(new \Twig_Extension_Debug());
        }
    }

    private function registerTranslator()
    {
        $this->register(new TranslationServiceProvider(), array(
            'locale_fallbacks' => array($this['config']['translator']['fallback_locale']),
        ));

        $extendTranslatorFunction = function (Translator $translator) {
            $translationFiles = glob($this['root_directory'] . '/app/resources/translations/*.yml');
            $translator->addLoader('yaml', new YamlFileLoader());
            foreach ($translationFiles as $file) {
                switch (substr($file, -6, 2)) {
                    case 'en':
                        $translator->addResource('yaml', $file, 'en');
                        break;
                    case 'de':
                        $translator->addResource('yaml', $file, 'de');
                        break;
                }
            }

            return $translator;
        };

        $this['translator'] = $this->share($this->extend('translator', $extendTranslatorFunction));
    }

    private function registerCustomServices()
    {
        $this['array.helper'] = function () {
            return new ArrayHelper();
        };

        $this['mail.placeholder_checker'] = function () {
            $twigTemplatePath = $this['root_directory'] . $this['config']['twig']['template_directory'];
            return new MailPlaceholderChecker($this['twig'], $twigTemplatePath);
        };

        $this['mail.content_generator'] = function ($this) {
            return new MailContentGenerator(
                $this['twig'],
                $this['mail.placeholder_checker'],
                $this['array.helper'],
                $this['config']['mail'],
                $this['translator']
            );
        };

        $this['mail.swift_mailer'] = function ($this) {
            return new SwiftMailer(
                $this['mailer'],
                $this['config']['mail'],
                $this['mail.logger']
            );
        };
    }

    private function registerConfig()
    {
        $this->register(new YamlConfigServiceProvider($this['root_directory'] . '/app/config/config.yml'));
    }

    private function enableJsonContentParsing()
    {
        $this->before(function (Request $request) {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new MailerException("Json decoding exception: " . json_last_error_msg());
                }

                $request->request->replace(is_array($data) ? $data : array());
            }
        });
    }

    /**
     * @return MailContentGenerator
     */
    public function getMailContentGenerator()
    {
        return $this['mail.content_generator'];
    }

    /**
     * @return SwiftMailer
     */
    public function getMailer()
    {
        return $this['mail.swift_mailer'];
    }
}
