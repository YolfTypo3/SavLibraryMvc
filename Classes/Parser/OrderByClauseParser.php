<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with TYPO3 source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace YolfTypo3\SavLibraryMvc\Parser;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Template Parser
 *
 * @package SavLibraryMvc
 */
class OrderByClauseParser
{

    const ORDER_BY_PATTERN = '/
    (?:
      (?:\s*,\s*)?
      (?P<clause>
        (?P<property>(?:(?:\w+\.)+)?\w+)
        (?i:\s+
          (?P<modifier>asc|desc)
        )?
      )
    )
  /x';

    /**
     * Processes the ORDER BY clause
     *
     * @param string $clause
     *
     * @return array
     */
    public function processOrderByClause(string $clause): array
    {
        $matches = [];
        preg_match_all(self::ORDER_BY_PATTERN, $clause, $matches);

        $result = [];
        foreach ($matches['property'] as $matchKey => $match) {
            $modifier = strtolower($matches['modifier'][$matchKey]);
            switch ($modifier) {
                case 'ASC':
                case 'asc':
                case '':
                    $result[$match] = QueryInterface::ORDER_ASCENDING;
                    break;
                case 'desc':
                case 'DESC':
                    $result[$match] = QueryInterface::ORDER_DESCENDING;
                    break;
            }
        }

        return $result;
    }
}
