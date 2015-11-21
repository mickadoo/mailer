<?php

namespace Mickadoo\Mailer\Service;

class MailPlaceholderChecker
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var string
     */
    protected $templateBaseFolder;

    /**
     * @param \Twig_Environment $twig
     * @param string $templateBaseFolder
     */
    public function __construct(\Twig_Environment $twig, $templateBaseFolder)
    {
        $this->twig = $twig;
        $this->templateBaseFolder = $templateBaseFolder;
    }

    /**
     * @param $twigTemplateName
     * @return array
     */
    public function getRequiredKeys($twigTemplateName)
    {
        $requiredKeys = [];

        $body = $this->twig->render($twigTemplateName, ['data' => []]);
        preg_match_all('/%[\w|\d|\.]*%/', $body, $missingKeys);

        if (isset($missingKeys[0])) {
            return array_merge($requiredKeys, $missingKeys[0]);
        }

        return $requiredKeys;
    }

    /**
     * @param $twigTemplateName
     * @param $translationData
     * @return array
     */
    public function getMissingKeys($twigTemplateName, $translationData)
    {
        return array_unique(
            array_diff($this->getRequiredKeys($twigTemplateName), array_keys($translationData))
        );
    }
}
