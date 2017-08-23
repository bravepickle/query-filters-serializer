<?php
/**
 * Value objects for fields' filters
 */

namespace Filter;


class FieldFilter implements \ArrayAccess, \JsonSerializable
{
    const KEY_CONSTRAINTS = 'constraints';
    const KEY_TYPE = 'type';
    const KEY_FIELD = 'field';

    /**
     * @var array
     */
    protected $constraints = [];

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $field;

    function __construct(array $data)
    {
        if ($data) {
            $this->setType($data[self::KEY_TYPE]);
            $this->setField($data[self::KEY_FIELD]);
            $this->setConstraints($data[self::KEY_CONSTRAINTS]);
        }
    }

    /**
     * @return array
     */
    public function getConstraints()
    {
        return $this->constraints;
    }

    /**
     * @param FieldConstraint[] $constraints
     * @throws ParsingException
     */
    public function setConstraints(array $constraints)
    {
        $mods = array();
        foreach ($constraints as $k => $mod) {
            if ($mod instanceof FieldConstraint) {
                $mods[$k] = $mod;
            } elseif (is_array($mod)) {
                $obj = new FieldConstraint($mod);
                $mods[$k] = $obj;
            } else {
                throw new ParsingException(sprintf('Unexpected type "%s"', gettype($mod)));
            }

        }

        $this->constraints = $mods;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            switch ($offset) {
                case self::KEY_CONSTRAINTS:
                    $this->setConstraints($value);
                    break;
                case self::KEY_TYPE:
                    $this->setType($value);
                    break;
                case self::KEY_FIELD:
                    $this->setField($value);
                    break;

                default:
                    new ParsingException(sprintf('Cannot set field "%s"', $offset));
            }

            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        switch ($offset) {
            case self::KEY_CONSTRAINTS:
                return $this->constraints === null;
            case self::KEY_TYPE:
                return $this->type === null;
            case self::KEY_FIELD:
                return $this->field === null;

            default:
                return false;
        }
    }

    public function offsetUnset($offset)
    {
        switch ($offset) {
            case self::KEY_CONSTRAINTS:
                $this->constraints = [];
                break;
            case self::KEY_TYPE:
                $this->type = null;
                break;
            case self::KEY_FIELD:
                $this->field = null;
                break;

            default:
                // Do nothing...
        }
    }

    public function offsetGet($offset)
    {
        switch ($offset) {
            case self::KEY_CONSTRAINTS:
                return $this->constraints;
            case self::KEY_TYPE:
                return $this->type;
            case self::KEY_FIELD:
                return $this->field;

            default:
                return null;
        }
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        return array(
            self::KEY_CONSTRAINTS => $this->constraints,
            self::KEY_TYPE => $this->type,
            self::KEY_FIELD => $this->field,
        );
    }

}
