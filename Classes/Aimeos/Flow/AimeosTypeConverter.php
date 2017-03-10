<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2015-2016
 * @package flow
 * @subpackage Flow
 */


namespace Aimeos\Flow;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Property\TypeConverter\MediaTypeConverter;
use Neos\Flow\Property\TypeConverter\MediaTypeConverterInterface;
use Neos\Flow\Property\PropertyMappingConfigurationInterface;


/**
 * Converter which transforms strings to arrays using the configured strategy.
 * This TypeConverter is used by the Aimeos shop package to decode only the required content
 * of a HTTP request which excludes json and xml based media types.
 *
 * @package flow
 * @subpackage Flow
 * @api
 * @Flow\Scope("singleton")
 */
class AimeosTypeConverter extends MediaTypeConverter implements MediaTypeConverterInterface
{
	protected $priority = -2;


	/**
	 * Convert the given $source to $targetType depending on the MediaTypeConverterInterface::CONFIGURATION_MEDIA_TYPE property mapping configuration
	 *
	 * @param string $source the raw request body
	 * @param string $targetType must be "array"
	 * @param array $convertedChildProperties
	 * @param PropertyMappingConfigurationInterface $configuration
	 * @return array
	 * @api
	 */
	public function convertFrom($source, $targetType, array $convertedChildProperties = array(), PropertyMappingConfigurationInterface $configuration = NULL)
	{
		$result = array();

		parse_str($source, $result);

		return $result;
	}
}
