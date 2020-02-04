<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\Path;
use App\Repository\LocationsRepository;
use Psr\Cache\InvalidArgumentException;

class PathResolver
{
    /** @var string */
    const FILE_PATH = __DIR__ . '/data/regions.json';

    /** @var string */
    const LOCATIONS_KEY = "locations_region";

    /** @var string */
    const ROOT = '3';

    private $levels = [
        '3' => '.*{1}',
        '5' => ['.2.*{1}', '.1.*{1}'],
        '7' => ['.5.*{1}', '.1.*{1}', '.0.0.1.*{1}', '.0.0.2.*{1}']
    ];

    /** @var LocationsRepository */
    private $repository;

    /** @var CacheDecorator */
    private $cacheDecorator;

    /**
     * PathResolver constructor.
     * @param CacheDecorator $cacheDecorator
     * @param LocationsRepository $repository
     */
    public function __construct(CacheDecorator $cacheDecorator, LocationsRepository $repository)
    {
        $this->cacheDecorator = $cacheDecorator;
        $this->repository = $repository;
    }

    /**
     * @param Path $path
     * @return void
     */
    public function getNormalizedData(Path $path): array
    {
        $normalizedData = [];
        if ($path->level !== self::ROOT) {
            foreach ($this->levels[$path->level] as $item) {
                $normalizedData[] = $path->path . $item;
            }
        }

        return $normalizedData;
    }

    /**
     * @param Path $path
     * @return array
     * @throws InvalidArgumentException
     */
    public function getData(Path $path): array
    {
        $term = \trim($path->term);
        $term = \mb_convert_case($term, MB_CASE_TITLE, "UTF-8");

        if ($path->level === self::ROOT) {
            $cacheData = $this->cacheDecorator->getCachedData(self::LOCATIONS_KEY);

            if ($cacheData === null) {
                $fileContent = \json_decode(\file_get_contents(self::FILE_PATH), true);
                $this->cacheDecorator->saveDataToCache(self::LOCATIONS_KEY, $fileContent);
                $cacheData = $fileContent;

            }
            return $this->filteredData($cacheData, $term);
        }

        $pathArray = $this->getNormalizedData($path);

        $result = $this->repository->getQueryLocationsDataFromArray($pathArray, $term . '%');
        $result = $this->parseResult($result);

        return $this->parseResult($result);
    }

    /**
     * @param string $path
     * @return array|null
     */
    public function splitPath(string $path): ?array
    {
        $splitPath = [];
        $split = \preg_split("/[\s.]+/", $path);
        $count = \count($split);

        if ($count >= 5) {
            $splitPath[0] = \implode(".", \array_slice($split, 0, 3));
            $splitPath[1] = \implode(".", \array_slice($split, 0, 5));
        }
        if ($count > 5) {
            $splitPath[2] = $path;
        }

        return $splitPath;
    }

    /**
     * @param array $data
     * @param string $title
     * @return array
     */
    private function filteredData(array $data, string $title): array
    {
      $result = \array_filter($data, function ($v) use ($title) {
            return \stripos($v['title'], $title) !== false;
        });

      return $this->parseResult($result);
    }

    /**
     * @param array $data
     * @return array
     */
    private function parseResult(array $data): array
    {
        $output = [];
        foreach ($data as $item){
            $output[] = $item;
        }

        return $output;
    }
}