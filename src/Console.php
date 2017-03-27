<?php
/**
 * Class Console | Console.php
 * @author Florian Knapp <office@florianknapp.de>
 */
namespace ConsoleCli;

/**
 * Class Console
 */
class Console
{

    /** @var array */
    private $args;

    /**
     * Console constructor.
     * @param array $args
     */
    public function __construct($args)
    {
        $this->args = $args;
    }

    /**
     * Return an option value
     *
     * @param string $opt
     * @return string
     * @throws \LogicException
     */
    public function getOpt($opt)
    {
        try {

            $result = $this->parseOptions();

            if (!empty($result[$opt])) {
                return $result[$opt];
            }

            throw new \InvalidArgumentException('No option with name "' . $opt . '" found.');

        } catch (\LogicException $e) {
            echo $e->getMessage() . "\n";
        }

        return null;
    }

    /**
     * Return if a long option value is set
     *
     * @param string $longOpt
     * @return boolean
     */
    public function getLongOpt($longOpt)
    {
        try {

            $result = $this->parseOptions();

            if (in_array($longOpt, $result['longOpts'])) {
                return true;
            } else if (in_array($longOpt, $result)) {
                throw new \LogicException('Long option was found in regular options set.');
            }

        } catch (\LogicException $e) {
            echo $e->getMessage() . "\n";
        }

        return false;
    }

    /**
     * Write message in console output
     *
     * @param string $message
     */
    public function write(string $message)
    {
        print $message . "\n";
    }

    /**
     * Parse options
     *
     * @return array
     */
    private function parseOptions()
    {
        $result  = [];
        $matches = [];
        $args    = array_splice($this->args, 1, count($this->args));
        $args    = implode(' ', $args);

        preg_match_all('/\-(?<opt>[a-zA-Z]+)[\s|=](?<value>[a-zA-Z0-9]+)|--(?<longOpt>[a-zA-Z0-9]+)/', $args, $matches);

        $opts     = self::filterEmpty($matches['opt']);
        $values   = self::filterEmpty($matches['value']);
        $longOpts = self::filterEmpty($matches['longOpt']);

        if (count($opts) !== count($values)) {
            throw new \LogicException('Option and value count doesn\'t match.');
        }

        for ($i=0; $i<count($opts); $i++) {

            if (empty($opts[$i]) || empty($values[$i])) {
                throw new \LogicException('Option or value mismatch. Please check your arguments.');
            }

            $result[$opts[$i]] = $values[$i];
        }

        foreach ($longOpts as $longOpt) {
            $result['longOpts'][] = $longOpt;
        }

        return $result;
    }

    /**
     * Filter empty array entries
     *
     * @param array $arr
     * @return array
     */
    private static function filterEmpty(array $arr)
    {
        return array_filter($arr, function($value) {
            return !empty($value);
        });
    }

}