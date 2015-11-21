<?php

namespace Mickadoo\Mailer\Service;

use Mickadoo\Mailer\Exception\MailerException;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment as Twig;

class MailContentGenerator
{
    const TWIG_FILE_SUFFIX = '.html.twig';

    /**
     * @var Twig
     */
    protected $twig;

    /**
     * @var MailPlaceholderChecker
     */
    protected $placeholderChecker;

    /**
     * @var ArrayHelper
     */
    protected $arrayHelper;

    /**
     * @var array
     */
    protected $mailConfig;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param Twig $twig
     * @param MailPlaceholderChecker $mailPlaceholderChecker
     * @param ArrayHelper $arrayHelper
     * @param array $mailConfig
     * @param TranslatorInterface $translator
     */
    public function __construct(
        Twig $twig,
        MailPlaceholderChecker $mailPlaceholderChecker,
        ArrayHelper $arrayHelper,
        array $mailConfig,
        TranslatorInterface $translator
    )
    {
        $this->twig = $twig;
        $this->placeholderChecker = $mailPlaceholderChecker;
        $this->arrayHelper = $arrayHelper;
        $this->mailConfig = $mailConfig;
        $this->translator = $translator;
    }

    /**
     * @param $templateType
     * @param $placeholderData
     * @param $locale
     * @return string
     */
    public function getBody($templateType, $placeholderData, $locale)
    {
        $translationData = $this->prepareData($placeholderData);
        $this->validateRequiredData($templateType, $translationData);
        $this->translator->setLocale($locale);

        return $this->twig->render($templateType . self::TWIG_FILE_SUFFIX, ['data' => $translationData]);
    }

    /**
     * @param $templateType
     * @param $data
     * @param $locale
     * @return string
     */
    public function getSubject($templateType, $data, $locale)
    {
        $this->translator->setLocale($locale);
        $placeHolderData = $this->prepareData($data);

        return $this->translator->trans(strtoupper($templateType. '.SUBJECT'), $placeHolderData);
    }

    /**
     * @param array $data
     * @return array
     */
    private function addDefaultData(array $data)
    {
        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    private function prepareData(array $data)
    {
        $data = $this->addDefaultData($data);

        return $this->arrayHelper->flattenAndDecorate($data);
    }

    /**
     * @param $type
     * @param array $translationData
     * @throws MailerException
     */
    private function validateRequiredData($type, array $translationData)
    {
        if ($missingKeys = $this->placeholderChecker->getMissingKeys($type . self::TWIG_FILE_SUFFIX, $translationData)) {
            throw new MailerException(
                "You're missing some data required for that mail. (" . implode(",", $missingKeys) . ")"
            );
        }
    }
}
