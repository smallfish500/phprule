<?php
/**
 * Template trait file
 * 
 * PHP version 7
 * 
 * @category Model
 * @package  Rule
 * @author   Jerome Lamartiniere <jerome@lamartiniere.eu>
 * @license  https://github.com/smallfish500/phprule/blob/master/LICENSE MIT
 * @link     https://github.com/smallfish500/phprule
 */
namespace Rule;

/**
 * Template trait
 * 
 * @category Model
 * @package  Rule
 * @author   Jerome Lamartiniere <jerome@lamartiniere.eu>
 * @license  https://github.com/smallfish500/phprule/blob/master/LICENSE MIT
 * @link     https://github.com/smallfish500/phprule
 */
trait Template
{
    /**
     * Diplay the data using Twig
     * or Json format if the JSON constant is true
     * 
     * @param array  $data Data that will be displayed
     * @param string $tpl  Template name
     *
     * @return void
     */
    public static function show($data, $tpl = 'index.html')
    {
        echo JSON
            ? json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE)
            : static::getTwig()->render($tpl, $data + ['base' => BASE.'/']);
    }

    /**
     * Returns the Twig environment
     *
     * @return \Twig_Environment
     */
    public static function getTwig()
    {
        static $twig;
        if (empty($twig)) {
            $twig = new \Twig\Environment(
                new \Twig\Loader\FilesystemLoader(TEMPLATES_DIR),
                ['debug' => DEBUG]
            );
        }

        return $twig;
    }
}
