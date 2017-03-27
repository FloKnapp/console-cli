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
    private $args = [];

    /** @var array */
    protected $expectedArgs = [];

    /** @var array */
    private $parsedOptions = [];

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
            self::write($e->getMessage());
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
            self::write($e->getMessage());
        }

        return false;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->getTarget('controller');
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->getTarget('action');
    }

    /**
     * Write message in console output
     *
     * @param string $message
     */
    public static function write(string $message)
    {
        print $message . "\n";
    }

    /**
     * @param string $type
     * @return string
     */
    private function getTarget($type = 'controller')
    {
        try {

            $result = $this->parseOptions();

            if (empty($result['target'])) {
                throw new \LogicException('No target given.');
            }

            $target = explode(':', $result['target']);

            switch ($type) {

                case 'controller':
                    return $target[0];

                case 'action':
                    return $target[1];

                default:
                    return '';

            }

        } catch (\LogicException $e) {
            self::write($e->getMessage());
        }

        return '';
    }

    /**
     * Parse options
     *
     * @return array
     */
    private function parseOptions()
    {
        if (!empty($this->parsedOptions))
            return $this->parsedOptions;

        $matches = [];
        $args    = array_splice($this->args, 1, count($this->args));
        $args    = implode(' ', $args);
        $regex   = '(?<target>[\w:\w]+)|\-(?<opt>[a-zA-Z]+)[\s|=](?<value>[\w]+)|--(?<longOpt>[\w]+)';

        preg_match_all('/' . $regex . '/', $args, $matches);

        $target   = self::filterEmpty($matches['target']);
        $opts     = self::filterEmpty($matches['opt']);
        $values   = self::filterEmpty($matches['value']);
        $longOpts = self::filterEmpty($matches['longOpt']);

        $result = $this->sanitizeOptions($target, $opts, $values, $longOpts);

        $this->parsedOptions = $result;

        return $result;
    }

    /**
     * Sanitize given options
     *
     * @param array $target
     * @param array $opts
     * @param array $values
     * @param array $longOpts
     * @return array
     */
    private function sanitizeOptions($target, $opts, $values, $longOpts)
    {
        $result = [];

        if (!empty($target[0]))
            $result['target'] = $target[0];

        if (count($opts) !== count($values))
            throw new \LogicException('Option and value count doesn\'t match.');

        for ($i=0; $i<count($opts); $i++) {

            if (empty($opts[$i]) || empty($values[$i]))
                throw new \LogicException('Option or value mismatch. Please check your arguments.');

            $result[$opts[$i]] = $values[$i];
        }

        foreach ($longOpts as $longOpt)
            $result['longOpts'][] = $longOpt;

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
        $result = array_filter($arr, function($value) {
            return !empty($value);
        });

        return array_values($result);
    }

}