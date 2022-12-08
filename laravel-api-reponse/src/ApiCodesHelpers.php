<?php
declare(strict_types=1);

namespace Taxionus\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2022 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Support\Facades\Config;
use Taxionus\ResponseBuilder\Exceptions as Ex;
use Taxionus\ResponseBuilder\ResponseBuilder as RB;

/**
 * Reusable ApiCodeBase related methods
 */
trait ApiCodesHelpers
{
	/**
	 * Returns lowest allowed error code for this module
	 *
	 * @return integer
	 *
	 * @throws Ex\MissingConfigurationKeyException Throws exception if no min_code set up
	 */
	public static function getMinCode(): int
	{
		$key = RB::CONF_KEY_MIN_CODE;
		$min_code = Config::get($key, null);

		if ($min_code === null) {
			throw new Ex\MissingConfigurationKeyException($key);
		}

		return $min_code;
	}

	/**
	 * Returns highest allowed error code for this module
	 *
	 * @return integer
	 *
	 * @throws Ex\MissingConfigurationKeyException Throws exception if no max_code set up
	 */
	public static function getMaxCode(): int
	{
		$key = RB::CONF_KEY_MAX_CODE;
		$max_code = Config::get($key, null);

		if ($max_code === null) {
			throw new Ex\MissingConfigurationKeyException($key);
		}

		return $max_code;
	}

	/**
	 * Returns array of error code constants defined in this class. Used mainly for debugging/tests
	 *
	 * @return array
	 */
	public static function getApiCodeConstants(): array
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return (new \ReflectionClass(static::class))->getConstants();
	}

	/**
	 * Returns complete error code to locale string mapping array
	 *
	 * @return array
	 *
	 * @throws Ex\IncompatibleTypeException
	 * @throws Ex\InvalidTypeException
	 * @throws Ex\MissingConfigurationKeyException Thrown when builder map is not configured.
	 * @throws Ex\NotArrayException
	 * @throws Ex\NotIntegerException
	 */
	public static function getMap(): array
	{
		$user_map = Config::get(RB::CONF_KEY_MAP, null);
		if ($user_map === null) {
			throw new Ex\MissingConfigurationKeyException(RB::CONF_KEY_MAP);
		}
		Validator::assertIsArray(RB::CONF_KEY_MAP, $user_map);

		return Util::mergeConfig(BaseApiCodes::getBaseMap(), $user_map);
	}

	/**
	 * Returns locale mappings key for given api code or @null if there's no mapping
	 *
	 * @param integer $api_code Api code to look for mapped message for.
	 *
	 * @return string|null
	 *
	 * @throws Ex\InvalidTypeException
	 * @throws Ex\NotIntegerException
	 * @throws Ex\MissingConfigurationKeyException
	 * @throws Ex\IncompatibleTypeException
	 */
	public static function getCodeMessageKey(int $api_code): ?string
	{
		if (!static::isCodeValid($api_code)) {
			$min = static::getMinCode();
			$max = static::getMaxCode();
			Validator::assertIsIntRange(
				'API code value', $api_code, $min, $max);
		}

		$map = static::getMap();

		return $map[ $api_code ] ?? null;
	}

	/**
	 * Checks if given API $code can be used in current configuration.
	 *
	 * @param int $code API code to validate
	 *
	 * @return bool
	 *
	 * @throws Ex\MissingConfigurationKeyException
	 */
	public static function isCodeValid(int $code): bool
	{
		return ($code === 0) || (($code >= static::getMinCode()) && ($code <= static::getMaxCode()));
	}

	/**
	 * Returns final API code for internal code, remapped to configured code range
	 *
	 * @param int $internal_code
	 *
	 * @return int
	 *
	 * @throws Ex\InvalidTypeException
	 * @throws Ex\MissingConfigurationKeyException
	 * @throws Ex\NotIntegerException
	 */
	public static function getCodeForInternalOffset(int $internal_code): int
	{
		$min = static::RESERVED_MIN_API_CODE_OFFSET;
		$max = static::RESERVED_MAX_API_CODE_OFFSET;
		Validator::assertIsIntRange('internal_code', $internal_code, $min, $max);

		return ($internal_code === 0) ? 0 : $internal_code + static::getMinCode();
	}

}
