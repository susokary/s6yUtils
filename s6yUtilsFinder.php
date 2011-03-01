<?php

/**
 * s6yUtilsFinder
 *
 * A class dedicated to the management of files research
 *
 * @author  Jérôme Pierre <contact@susokary.com>
 * @version V1 - March 1st 2011
 *
 * @see http://www.symfony-project.org/api/1_4/sfFinder
 *
 * Example:
 *
 * $images = s6yUtilsFinder::getNewInstance()
 *     ->setType('file')
 *     ->addNames('*.jpg', '*.gif')
 *     ->addSizes('>= 10Ki')
 *     ->searchIn(Mage::getBaseDir().'/skin');
 *
 * $modules = s6yUtilsFinder::getNewInstance()
 *     ->setType('directory')
 *     ->setMaximumDepth(1)
 *     ->returnRelativePaths(true)
 *     ->searchIn(Mage::getBaseDir().'/app/code/local/Cylande');
 */
class s6yUtilsFinder
{

    /**
     * The type of elements that must be returned
     *
 	 * @author Jérôme Pierre <contact@susokary.com>
 	 *
 	 * Comment:
 	 * Must be "file", "directory" or "any".
 	 *
 	 * @var string $_type
     */
    private $_type = 'any';

    /**
     * The list of patterns of names that must or mustn't match
     *
 	 * @author Jérôme Pierre <contact@susokary.com>
 	 *
 	 * @var array $_names
     */
    private $_names = array();

    /**
     * The list of size rules that must match
     *
 	 * @author Jérôme Pierre <contact@susokary.com>
 	 *
 	 * @var array $_sizes
     */
    private $_sizes = array();

    /**
     * The list of functions and/or methods that must match
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @var array $_execs
     */
    private $_execs = array();

    /**
     * The lists of patterns of names that must be pruned and/or discarded
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @var array $_prunes
     * @var array $_discards
     */
    private $_prunes   = array();
    private $_discards = array();

    /**
     * The minimum and maximum depth of the research to perform
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @var integer $_minimumDepth
     * @var integer $_maximumDepth
     */
    private $_minimumDepth = 0;
    private $_maximumDepth = 666;

    /**
     * The type of order by that must be applied
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @var boolean|string $_sort
     */
    private $_sort = 'type';

    /**
     * Says if version control directories must or mustn't be ignored
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @var boolean $_ignoreVersionControl
     */
    private $_ignoreVersionControl = true;


    /**
     * Says if relative or absolute paths must be returned
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @var boolean $_returnRelativePaths
     */
    private $_returnRelativePaths = false;

    /**
     * Says if symlinks must or mustn't be followed
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @var boolean $_followSymlinks
     */
    private $_followSymlinks = false;

    /**
     * The flags that signal leading points and wildcard slashes
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @var boolean $_strictLeadingDot
     * @var boolean $_strictWildcardSlash
     */
    static private $_strictLeadingDot    = true;
    static private $_strictWildcardSlash = true;

    /**
     * Some exception messages
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @var string EXCP_01
     * @var string EXCP_02
     * @var string EXCP_03
     * @var string EXCP_04
     */
    const EXCP_01 = 'Invalid usage of function %1$s().';
    const EXCP_02 = 'Call to undefined function %1$s().';
    const EXCP_03 = 'Invalid usage of method %1$s::%2$s().';
    const EXCP_04 = 'Call to undefined method %1$s::%2$s().';

    /**
     * Provides an instance of this class
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @return Cylande_Utils_FilesFinder A new instance of this class
     */
    static public function getInstance()
    {
        return new self();
    }

    /**
     * Defines the type of elements that must be returned
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @param string $type A given type
     *
     * @return Cylande_Utils_FilesFinder The current instance of this class
     */
    public function setType($type)
    {
        switch (strtolower($type)) {
            case 'file':
            case 'files':
                $this->_type = 'file';
                break;

            case 'dir':
            case 'directory':
            case 'directories':
                $this->_type = 'directory';
                break;

            default:
                $this->_type = 'any';
                break;
        }

        return $this;
    }

    /**
     * Adds a list of patterns of names that must match
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * Example:
     *
     * // Patterns (delimited by slashes), globs or simple strings can be used
     *
     * $this->addNames('/\.php$/')
     * $this->addNames('*.php', '*.yml')
     * $this->addNames('test.php')
     *
     * @param mixed A given list of patterns of names
     *
     * @return Cylande_Utils_FilesFinder The current instance of this class
     */
    public function addNames()
    {
        $arguments = $this->_getArgumentsAsArray(func_get_args());

        $this->_names = array_merge($this->_names, $arguments);

        return $this;
    }

    /**
     * Adds a list of patterns of names that mustn't match
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @param mixed A given list of patterns of names
     *
     * @return Cylande_Utils_FilesFinder The current instance of this class
     *
     * @see Cylande_Utils_FilesFinder::addNames()
     */
    public function addNotNames()
    {
        $arguments = $this->_getArgumentsAsArray(func_get_args(), true);

        $this->_names = array_merge($this->_names, $arguments);

        return $this;
    }

    /**
     * Adds a list of size rules that must match
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * Example:
     *
     * // Integer or more complex comparisons can be used
     *
     * $this->addSizes('> 10K');
     * $this->addSizes('<= 1Ki');
     * $this->addSizes(4);
     *
     * @param mixed A given list of size rules
     *
     * @return Cylande_Utils_FilesFinder The current instance of this class
     */
    public function addSizes()
    {
        $arguments = func_get_args();

        $this->_sizes = array_merge($this->_sizes, $arguments);

        return $this;
    }

    /**
     * Adds a list of functions and/or methods that must match
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * Example:
     *
     * // Functions or methods can be used
     *
     * $this->addExecs('myFunction');
     * $this->addExecs('myFirstFunction', 'mySecondFunction');
     * $this->addExecs(array($myObject, 'myMethod'));
     *
     * @param mixed A given list of functions and/or methods
     *
     * @return Cylande_Utils_FilesFinder The current instance of this class
     */
    public function addExecs()
    {
        $arguments = func_get_args();

        $this->_execs = array_merge($this->_execs, $arguments);

        return $this;
    }

    /**
     * Adds a list of patterns of names that must be pruned
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @param mixed A given list of patterns of names
     *
     * @return Cylande_Utils_FilesFinder The current instance of this class
     *
     * @see Cylande_Utils_FilesFinder::addNames()
     */
    public function addPrunes()
    {
        $arguments = $this->_getArgumentsAsArray(func_get_args());

        $this->_prunes = array_merge($this->_prunes, $arguments);

        return $this;
    }

    /**
     * Adds a list of patterns of names that must be discarded
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @param mixed A given list of patterns of names
     *
     * @return Cylande_Utils_FilesFinder The current instance of this class
     *
     * @see Cylande_Utils_FilesFinder::addNames()
     */
    public function addDiscards()
    {
        $arguments = $this->_getArgumentsAsArray(func_get_args());

        $this->_discards = array_merge($this->_discards, $arguments);

        return $this;
    }

    /**
     * Defines the minimum depth of the research to perform
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @param integer $depth A given depth
     *
     * @return Cylande_Utils_FilesFinder The current instance of this class
     */
    public function setMinimumDepth($depth)
    {
        $this->_minimumDepth = (integer) $depth - 1;

        return $this;
    }

    /**
     * Defines the maximum depth of the research to perform
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @param integer $depth A given depth
     *
     * @return Cylande_Utils_FilesFinder The current instance of this class
     */
    public function setMaximumDepth($depth)
    {
        $this->_maximumDepth = (integer) $depth - 1;

        return $this;
    }

    /**
     * Sorts directories and files by their names
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @return Cylande_Utils_FilesFinder The current instance of this class
     */
    public function sortByName()
    {
        $this->_sort = 'name';

        return $this;
    }

    /**
     * Sorts directories and files by their types
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @return Cylande_Utils_FilesFinder The current instance of this class
     */
    public function sortByType()
    {
        $this->_sort = 'type';

        return $this;
    }

    /**
     * Ignores or not version control directories
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * Comment:
     *
     * Currently supports:
     *  - Bazaar-NG ;
     *  - CVS ;
     *  - DARCS ;
     *  - GIT ;
     *  - Gnu Arch ;
     *  - Mercurial ;
     *  - Monotone ;
     *  - and Subversion.
     *
     * @param boolean $flag Says if they must or mustn't be ignored
     *
     * @return Cylande_Utils_FilesFinder The current instance of this class
     */
    public function ignoreVersionControl($flag = true)
    {
        $this->_ignoreVersionControl = $flag;

        return $this;
    }

    /**
     * Returns or not relative paths for all directories and files
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @param boolean $flag Says if relative or absolute paths must be returned
     *
     * @return Cylande_Utils_FilesFinder The current instance of this class
     */
    public function returnRelativePaths($flag = true)
    {
        $this->_returnRelativePaths = $flag;

        return $this;
    }

    /**
     * Follows or not symlinks
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @param boolean $flag Says if symlinks must or mustn't be followed
     *
     * @return Cylande_Utils_FilesFinder The current instance of this class
     */
    public function followSymlinks($flag = true)
    {
        $this->_followSymlinks = $flag;

        return $this;
    }

    /**
     * Searches in given directories in accordance with all constraints set
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @param mixed A given list of directories
     *
     * @return array $files The directories and/or files found
     */
    public function searchIn()
    {
        $files = array();

        $finder = clone $this;

        if ($this->_ignoreVersionControl) {
            $ignores = array(
            '.svn',
            '_svn',
            'CVS',
            '_darcs',
            '.arch-params',
            '.monotone',
            '.bzr',
            '.git',
            '.hg',
            );

            $finder->addPrunes($ignores);

            $finder->addDiscards($ignores);
        }

        $arguments = func_get_args();

        if (is_array($arguments[0]) && func_num_args() === 1) {
            $arguments = array_shift($arguments);
        }

        foreach ($arguments as $argument) {
            $directory = realpath($argument);

            if (!is_dir($directory)) {
                continue;
            }

            $directory = str_replace('\\', '/', $directory);

            if (!$this->_isAbsolutePath($directory)) {
                $directory = getcwd()."/$directory";
            }

            $result = str_replace('\\', '/', $finder->_search($directory));

            if ($this->_returnRelativePaths) {
                $result = str_replace(rtrim($directory, '/').'/', '', $result);
            }

            $files = array_merge($files, $result);
        }

        if ($this->_sort === 'name') {
            sort($files);
        }

        $files = array_unique($files);

        return $files;
    }

    /**
     * Searches in a given directory and at a given depth
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @param string  $directory A given directory
     * @param integer $depth  A given depth
     *
     * @return array $files The directories and/or files found
     */
    private function _search($directory, $depth = 0)
    {
        if ($depth > $this->_maximumDepth) {
            return array();
        }

        $directory = realpath($directory);

        if (is_link($directory) && !$this->_followSymlinks) {
            return array();
        }

        $files           = array();
        $tempFiles       = array();
        $tempDirectories = array();

        if (is_dir($directory) && is_readable($directory)) {
            $currentDirectory = opendir($directory);

            while ($entry = readdir($currentDirectory)) {
                if (in_array($entry, array('.', '..'))) {
                    continue;
                }

                $currentEntry = "$directory/$entry";

                if (is_link($currentEntry) && !$this->_followSymlinks) {
                    continue;
                }

                if (is_dir($currentEntry)) {
                    if ($this->_sort === 'type') {
                        $tempDirectories[$entry] = $currentEntry;
                    } else {
                        if (in_array($this->_type, array('directory', 'any'))
                            && $depth >= $this->_minimumDepth
                            && !$this->_checkDiscards($currentEntry)
                            && $this->_checkNames($currentEntry)
                            && $this->_checkExecs($currentEntry)
                        ) {
                            $files[] = $currentEntry;
                        }

                        if (!$this->_checkPrunes($currentEntry)) {
                            $result = $this->_search($currentEntry, $depth + 1);

                            $files = array_merge($files, $result);
                        }
                    }
                } else {
                    if (in_array($this->_type, array('file', 'any'))
                        && $depth >= $this->_minimumDepth
                        && !$this->_checkDiscards($currentEntry)
                        && $this->_checkNames($currentEntry)
                        && $this->_checkSizes($currentEntry)
                        && $this->_checkExecs($currentEntry)
                    ) {
                        if ($this->_sort === 'type') {
                            $tempFiles[] = $currentEntry;
                        } else {
                            $files[] = $currentEntry;
                        }
                    }
                }
            }

            if ($this->_sort === 'type') {
                ksort($tempDirectories);

                foreach ($tempDirectories as $entry => $currentEntry) {
                    if (in_array($this->_type, array('directory', 'any'))
                        && $depth >= $this->_minimumDepth
                        && !$this->_checkDiscards($currentEntry)
                        && $this->_checkNames($currentEntry)
                        && $this->_checkExecs($currentEntry)
                    ) {
                        $files[] = $currentEntry;
                    }

                    if (!$this->_checkPrunes($currentEntry)) {
                        $result = $this->_search($currentEntry, $depth + 1);

                        $files = array_merge($files, $result);
                    }
                }

                sort($tempFiles);

                $files = array_merge($files, $tempFiles);
            }

            closedir($currentDirectory);
        }

        return $files;
    }

    /**
     * Checks if the name of a given entry matches or not the patterns set
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @param string $entry A given entry
     *
     * @return boolean The appropriate answer
     */
    private function _checkNames($entry)
    {
        if (!count($this->_names)) {
            return true;
        }

        $entry = basename($entry);

        $oneNameRule    = false;
        $oneNotNameRule = false;

        foreach ($this->_names as $name) {
            $not   = $name[0];
            $regex = $name[1];

            if ($not) {
                $oneNotNameRule = true;
            } else {
                $oneNameRule = true;
            }

            if (preg_match($regex, $entry)) {
                if ($not) {
                    return false;
                } else {
                    return true;
                }
            }
        }

        if ($oneNotNameRule && $oneNameRule) {
            return false;
        } elseif ($oneNotNameRule) {
            return true;
        } elseif ($oneNameRule) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the filesize of a given entry matches or not the rules set
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @param string $entry A given entry
     *
     * @return boolean The appropriate answer
     */
    private function _checkSizes($entry)
    {
        if (is_array($this->_sizes)
            && count($this->_sizes)
            && is_file($entry)
        ) {
            $filesize = filesize($entry);
            $pattern  = '{^([<>]=?)?(.*?)([kmg]i?)?$}i';

            foreach ($this->_sizes as $size) {
                if (!preg_match($pattern, $size, $matches)) {
                    throw new Exception(sprintf(
                        self::EXCP_03,
                        __CLASS__,
                        'addSizes'
                    ));
                }

                $comparison = '';
                $target     = '';
                $magnitude  = '';

                if (array_key_exists(1, $matches)) {
                    $comparison = $matches[1];
                }

                if (array_key_exists(2, $matches)) {
                    $target = $matches[2];
                }

                if (array_key_exists(3, $matches)) {
                    $magnitude = strtolower($matches[3]);
                }

                switch ($magnitude) {
                    case 'k':
                        $target *= 1000;
                        break;

                    case 'ki':
                        $target *= 1024;
                        break;

                    case 'm':
                        $target *= 1000 * 1000;
                        break;

                    case 'mi':
                        $target *= 1024 * 1024;
                        break;

                    case 'g':
                        $target *= 1000 * 1000 * 1000;
                        break;

                    case 'gi':
                        $target *= 1024 * 1024 * 1024;
                        break;
                }

                switch ($comparison) {
                    case '':
                    case '==':
                    case '===':
                        return ($filesize === $target);
                        break;

                    case '>':
                        return ($filesize > $target);
                        break;

                    case '>=':
                        return ($filesize >= $target);
                        break;

                    case '<':
                        return ($filesize < $target);
                        break;

                    case '<=':
                        return ($filesize <= $target);
                        break;

                    default:
                        return false;
                        break;
                }
            }
        }

        return true;
    }

    /**
     * Checks if a given entry matches or not the functions and/or methods set
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @param string $entry A given entry
     *
     * @return boolean The appropriate answer
     */
    private function _checkExecs($entry)
    {
        if (is_array($this->_execs) && count($this->_execs)) {
            foreach ($this->_execs as $exec) {
                if (is_array($exec)) {
                    if (!isset($exec[0]) || !isset($exec[1])) {
                        throw new Exception(sprintf(
                            self::EXCP_03,
                            __CLASS__,
                            'addExecs'
                        ));
                    }

                    if (!method_exists($exec[0], $exec[1])) {
                        throw new Exception(sprintf(
                            self::EXCP_04,
                            (string) get_class($exec[0]),
                            (string) $exec[1]
                        ));
                    }
                } else {
                    if (!function_exists($exec)) {
                        throw new Exception(sprintf(
                            self::EXCP_02,
                            (string) $exec
                        ));
                    }
                }

                if (!call_user_func_array($exec, $entry)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Says if a given entry is pruned or not
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @param string $entry A given entry
     *
     * @return boolean The appropriate answer
     */
    private function _checkPrunes($entry)
    {
        if (is_array($this->_prunes) && count($this->_prunes)) {
            $entry = basename($entry);

            foreach ($this->_prunes as $prune) {
                if (preg_match($prune[1], $entry)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Says if a given entry is discarded or not
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @param string $entry A given entry
     *
     * @return boolean The appropriate answer
     */
    private function _checkDiscards($entry)
    {
        if (is_array($this->_discards) && count($this->_discards)) {
            $entry = basename($entry);

            foreach ($this->_discards as $discard) {
                if (preg_match($discard[1], $entry)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Says if a given path is absolute or not
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @param string $path A given path
     *
     * @return boolean The appropriate answer
     */
    private function _isAbsolutePath($path)
    {
        switch ($path{0}) {
            case '/':
            case '\\':
                return true;
                break;

            default:
                if (strlen($path) > 3
                    && ctype_alpha($path{0})
                    && $path{1} === ':'
                    && ($path{2} === '\\' || $path{2} === '/')
                    ) {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * Provides a structured list of arguments from a given varied one
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * @param array   $arguments A given varied list of arguments
     * @param boolean $not       Says if it must or musn't match
     *
     * @return array $list The relevant structured list of arguments
     */
    private function _getArgumentsAsArray($arguments, $not = false)
    {
        $list = array();

        if (is_array($arguments)) {
            foreach ($arguments as $argument) {
                if (is_array($argument)) {
                    foreach ($argument as $subArgument) {
                        $list[] = array(
                            $not,
                            $this->_getArgumentAsRegex($subArgument),
                        );
                    }
                } else {
                    $list[] = array(
                        $not,
                        $this->_getArgumentAsRegex($argument),
                    );
                }
            }
        }

        return $list;
    }

    /**
     * Provides a regular expression from a given argument
     *
     * @author Jérôme Pierre <contact@susokary.com>
     *
     * Example:
     *
     * // Prints "foo.bar" and "foo.baz"
     *
     * $regex = $this->_getArgumentAsRegex("foo.*");
     *
     * $entries = array(
     *     'foo.bar',
     *     'foo.baz',
     *     'foo',
     *     'bar',
     *     'baz',
     * );
     *
     * foreach ($entries as $entry) {
     *     if (preg_match($regex, $entry)) {
     *       print "$entry\n";
     *     }
     * }
     *
     * @param string $argument A given argument
     *
     * @return string $regex The relevant regular expression
     *
     * @see Perl Text::Glob module
     */
    private function _getArgumentAsRegex($argument)
    {
        $firstByte = true;
        $isEscaped = false;
        $inCurlies = 0;

        $regex = '';

        for ($m = strlen($argument), $i = 0; $i < $m; $i++) {
            $car = $argument[$i];

            if ($firstByte) {
                if ($car !== '.' && self::$_strictLeadingDot) {
                    $regex .= '(?=[^\.])';
                }

                $firstByte = false;
            }

            switch ($car) {
                case '/':
                    $firstByte = true;
                    break;

                case '.':
                case '(':
                case ')':
                case '|':
                case '+':
                case '^':
                case '$':
                    $regex .= "\\$car";
                    break;

                case '*':
                    if ($isEscaped) {
                        $regex .= '\\*';
                    } elseif (self::$_strictWildcardSlash) {
                        $regex .= '[^/]*';
                    } else {
                        $regex .= '.*';
                    }
                    break;

                case '?':
                    if ($isEscaped) {
                        $regex .= '\\?';
                    } elseif (self::$_strictWildcardSlash) {
                        $regex .= '[^/]';
                    } else {
                        $regex .= '.';
                    }
                    break;

                case '{':
                    if ($isEscaped) {
                        $regex .= '\\{';
                    } else {
                        $regex .= '(';

                         $inCurlies++;
                    }
                    break;

                case '}':
                    if ($inCurlies) {
                        if ($isEscaped) {
                            $regex .= '}';
                        } else {
                            $regex .= ')';

                             $inCurlies--;
                        }
                    }
                    break;

                case ',':
                    if ($inCurlies) {
                        if ($isEscaped) {
                            $regex .= ',';
                        } else {
                            $regex .= '|';
                        }
                    }
                    break;

                case '\\':
                    if ($isEscaped) {
                        $regex .= '\\\\';

                        $isEscaped = false;
                    } else {
                        $isEscaped = true;
                    }

                    continue;
                    break;

                default:
                    $regex .= $car;
                    break;
            }

            $isEscaped = false;
        }

        $regex = "#^$regex$#";

        return $regex;
    }

}
