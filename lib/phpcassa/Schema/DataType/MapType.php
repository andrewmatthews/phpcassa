<?php
namespace phpcassa\Schema\DataType;

use phpcassa\Schema\DataType\CassandraType;

/**
 * Holds multiple types as subcomponents.
 *
 */
class MapType extends CassandraType
{
    /**
     * @param phpcassa\Schema\DataType\CassandraType[] $inner_types an array
     *        of other CassandraType instances.
     */
    public function __construct($inner_types) {
        $this->inner_types = $inner_types;
    }

	/**
	 * @todo: make this work
	 */
    public function pack($value, $is_name=true, $slice_end=null, $is_data=false) {
        throw new \Exception('MapType->pack() has not been implemented');
    }

    public function unpack($data, $is_name=true) {
        $bytes			= unpack("Chi/Clow", substr($data, 0, 2));
        $num_elements	= $bytes["hi"]*256 + $bytes["low"];
        $data	= substr($data, 2);
        
        $components = array();
		for ( $i = 0; $i < $num_elements; $i++ ) {
			// Extract map key
			$bytes = unpack("Chi/Clow", substr($data, 0, 2));
			$len = $bytes["hi"]*256 + $bytes["low"];
			$type = $this->inner_types[0];
			$map_key = $type->unpack(substr($data, 2, $len), false);
			$data = substr($data, $len + 2);
			
			// Extract map value
			$bytes = unpack("Chi/Clow", substr($data, 0, 2));
			$len = $bytes["hi"]*256 + $bytes["low"];
			$type = $this->inner_types[1];
			$map_value = $type->unpack(substr($data, 2, $len), false);
			$data	= substr($data, $len + 2);
			
			$components[$map_key] = $map_value;
		}

        if ($is_name) {
            return serialize($components);
        } else {
            return $components;
        }
    }

    public function __toString() {
        $inner_strs = array();
        foreach ($this->inner_types as $inner_type) {
            $inner_strs[] = (string)$inner_type;
        }

        return 'MapType(' . join(', ', $inner_strs) . ')';
    }
}
