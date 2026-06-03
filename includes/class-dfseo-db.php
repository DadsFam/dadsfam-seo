<?php
/**
 * Database helper for DadsFam SEO.
 * Creates and manages custom tables.
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DFSEO_DB {

	// Table names (without prefix)
	const TABLE_REDIRECTS    = 'dfseo_redirects';
	const TABLE_404_LOG      = 'dfseo_404_log';
	const TABLE_RANKINGS     = 'dfseo_keyword_rankings';
	const TABLE_ANALYTICS    = 'dfseo_analytics';

	/**
	 * Create all custom tables on activation.
	 */
	public static function create_tables(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();
		$pfx     = $wpdb->prefix;

		// Redirects
		dbDelta( "CREATE TABLE IF NOT EXISTS `{$pfx}dfseo_redirects` (
			`id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			`source_url`   VARCHAR(2048)   NOT NULL DEFAULT '',
			`target_url`   VARCHAR(2048)   NOT NULL DEFAULT '',
			`redirect_type` SMALLINT       NOT NULL DEFAULT 301,
			`enabled`      TINYINT(1)      NOT NULL DEFAULT 1,
			`hit_count`    BIGINT UNSIGNED NOT NULL DEFAULT 0,
			`note`         VARCHAR(255)    NOT NULL DEFAULT '',
			`created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			KEY `source_url` (`source_url`(191)),
			KEY `enabled` (`enabled`)
		) $charset;" );

		// 404 log
		dbDelta( "CREATE TABLE IF NOT EXISTS `{$pfx}dfseo_404_log` (
			`id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			`url`         VARCHAR(2048)   NOT NULL DEFAULT '',
			`referrer`    VARCHAR(2048)   NOT NULL DEFAULT '',
			`user_agent`  VARCHAR(512)    NOT NULL DEFAULT '',
			`ip_hash`     VARCHAR(64)     NOT NULL DEFAULT '',
			`hit_count`   BIGINT UNSIGNED NOT NULL DEFAULT 1,
			`created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`last_seen`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			KEY `url` (`url`(191)),
			KEY `last_seen` (`last_seen`)
		) $charset;" );

		// Keyword rankings (manual tracking)
		dbDelta( "CREATE TABLE IF NOT EXISTS `{$pfx}dfseo_keyword_rankings` (
			`id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			`keyword`        VARCHAR(512)    NOT NULL DEFAULT '',
			`target_url`     VARCHAR(2048)   NOT NULL DEFAULT '',
			`position`       SMALLINT        NOT NULL DEFAULT 0,
			`search_engine`  VARCHAR(32)     NOT NULL DEFAULT 'google',
			`date_recorded`  DATE            NOT NULL,
			`created_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			KEY `keyword` (`keyword`(191)),
			KEY `date_recorded` (`date_recorded`)
		) $charset;" );

		// Analytics (basic internal click/impression tracking)
		dbDelta( "CREATE TABLE IF NOT EXISTS `{$pfx}dfseo_analytics` (
			`id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			`post_id`         BIGINT UNSIGNED NOT NULL DEFAULT 0,
			`organic_clicks`  INT UNSIGNED    NOT NULL DEFAULT 0,
			`impressions`     INT UNSIGNED    NOT NULL DEFAULT 0,
			`avg_position`    DECIMAL(6,2)    NOT NULL DEFAULT 0.00,
			`ctr`             DECIMAL(5,4)    NOT NULL DEFAULT 0.0000,
			`date_recorded`   DATE            NOT NULL,
			`created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `post_date` (`post_id`, `date_recorded`),
			KEY `post_id` (`post_id`),
			KEY `date_recorded` (`date_recorded`)
		) $charset;" );

		update_option( 'dfseo_db_version', '1.0.0' );
	}

	/**
	 * Drop all custom tables on uninstall.
	 */
	public static function drop_tables(): void {
		global $wpdb;
		$pfx = $wpdb->prefix;
		foreach ( [ 'dfseo_redirects', 'dfseo_404_log', 'dfseo_keyword_rankings', 'dfseo_analytics' ] as $table ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "DROP TABLE IF EXISTS `{$pfx}{$table}`" );
		}
	}

	// ─── Helpers ────────────────────────────────────────────────────────────

	public static function table( string $name ): string {
		global $wpdb;
		return $wpdb->prefix . 'dfseo_' . $name;
	}

	/**
	 * Paginated query helper.
	 *
	 * @return array{ items: array, total: int }
	 */
	public static function paginate( string $table, array $where = [], int $per_page = 20, int $page = 1, string $order_by = 'id', string $order = 'DESC' ): array {
		global $wpdb;
		$offset = ( max( 1, $page ) - 1 ) * $per_page;

		$where_sql  = '';
		$where_vals = [];
		if ( ! empty( $where ) ) {
			$clauses = [];
			foreach ( $where as $col => $val ) {
				$clauses[]    = "`{$col}` = %s";
				$where_vals[] = $val;
			}
			$where_sql = 'WHERE ' . implode( ' AND ', $clauses );
		}

		$order_by = sanitize_sql_orderby( $order_by . ' ' . $order ) ?: 'id DESC';

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		// Build args arrays to avoid PHP 8.1+ spread-then-positional syntax
		$count_args = $where_vals;
		$list_args  = array_merge( $where_vals, [ $per_page, $offset ] );

		$total = (int) $wpdb->get_var(
			$where_vals
				? $wpdb->prepare( "SELECT COUNT(*) FROM `{$table}` {$where_sql}", ...$count_args )
				: "SELECT COUNT(*) FROM `{$table}` {$where_sql}"
		);

		$items = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM `{$table}` {$where_sql} ORDER BY {$order_by} LIMIT %d OFFSET %d", ...$list_args ),
			ARRAY_A
		);
		// phpcs:enable

		return [
			'items' => $items ?: [],
			'total' => $total,
			'pages' => $total > 0 ? (int) ceil( $total / $per_page ) : 0,
		];
	}
}
