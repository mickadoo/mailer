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
        $source = $this->twig->getLoader()->getSource($twigTemplateName);
        $tokens = $this->twig->tokenize($source);
        $parsed = $this->twig->getParser()->parse($tokens);
        $collected = [];
        $this->collectNodes($parsed, $collected);

        return array_keys($collected);
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

    /**
     * @param \Twig_Node $nodes
     * @param array $collected
     */
    private function collectNodes($nodes, array &$collected)
    {
        foreach ($nodes as $node) {
            $childNodes = $node->getIterator()->getArrayCopy();
            if (!empty($childNodes)) {
                $this->collectNodes($childNodes, $collected);
            } elseif ($node instanceof \Twig_Node_Expression_Name) {
                $name = $node->getAttribute('name');
                $collected[$name] = $node; // ensure unique values
            }
        }
    }

}
