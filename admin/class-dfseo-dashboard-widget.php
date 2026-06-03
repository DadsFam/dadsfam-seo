<?php
/**
 * WordPress Dashboard Widget — quick SEO health overview.
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DFSEO_Dashboard_Widget {

	public function __construct() {
		add_action( 'wp_dashboard_setup', [ $this, 'register_widget' ] );
	}

	public function register_widget(): void {
		wp_add_dashboard_widget(
			'dfseo_dashboard_widget',
			__( 'DadsFam SEO — Site Health', 'dadsfam-seo' ),
			[ $this, 'render' ]
		);
	}

	public function render(): void {
		$totals = $this->get_seo_health();
		?>
		<div class="dfseo-dw">
			<div class="dfseo-dw-scores">
				<div class="dfseo-dw-item great">
					<strong><?php echo esc_html( $totals['great'] ); ?></strong>
					<span><?php esc_html_e( 'Great (80+)', 'dadsfam-seo' ); ?></span>
				</div>
				<div class="dfseo-dw-item ok">
					<strong><?php echo esc_html( $totals['ok'] ); ?></strong>
					<span><?php esc_html_e( 'Needs Work (50–79)', 'dadsfam-seo' ); ?></span>
				</div>
				<div class="dfseo-dw-item poor">
					<strong><?php echo esc_html( $totals['poor'] ); ?></strong>
					<span><?php esc_html_e( 'Poor (0–49)', 'dadsfam-seo' ); ?></span>
				</div>
				<div class="dfseo-dw-item na">
					<strong><?php echo esc_html( $totals['na'] ); ?></strong>
					<span><?php esc_html_e( 'Not analysed', 'dadsfam-seo' ); ?></span>
				</div>
			</div>

			<?php if ( ! empty( $totals['needs_attention'] ) ) : ?>
			<h4><?php esc_html_e( 'Needs attention', 'dadsfam-seo' ); ?></h4>
			<ul class="dfseo-dw-list">
				<?php foreach ( $totals['needs_attention'] as $p ) : ?>
				<li>
					<a href="<?php echo esc_url( get_edit_post_link( $p['id'] ) ); ?>"><?php echo esc_html( $p['title'] ); ?></a>
					<span class="dfseo-score-badge <?php echo esc_attr( $p['grade'] ); ?>"><?php echo esc_html( $p['score'] ); ?></span>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>

			<p style="margin-top:12px">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=dfseo-dashboard' ) ); ?>" class="button"><?php esc_html_e( 'View Full Dashboard', 'dadsfam-seo' ); ?></a>
			</p>
		</div>
		<?php
	}

	private function get_seo_health(): array {
		$query = new WP_Query( [
			'post_type'      => [ 'post', 'page' ],
			'post_status'    => 'publish',
			'posts_per_page' => 200,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		] );

		$totals = [ 'great' => 0, 'ok' => 0, 'poor' => 0, 'na' => 0, 'needs_attention' => [] ];

		foreach ( $query->posts as $id ) {
			$score = (int) get_post_meta( $id, '_dfseo_score', true );
			$kw    = get_post_meta( $id, '_dfseo_focus_keyword', true );
			if ( ! $kw ) {
				$totals['na']++;
			} elseif ( $score >= 80 ) {
				$totals['great']++;
			} elseif ( $score >= 50 ) {
				$totals['ok']++;
				if ( count( $totals['needs_attention'] ) < 5 ) {
					$totals['needs_attention'][] = [ 'id' => $id, 'title' => get_the_title( $id ), 'score' => $score, 'grade' => 'ok' ];
				}
			} else {
				$totals['poor']++;
				if ( count( $totals['needs_attention'] ) < 5 ) {
					$totals['needs_attention'][] = [ 'id' => $id, 'title' => get_the_title( $id ), 'score' => $score, 'grade' => 'poor' ];
				}
			}
		}

		usort( $totals['needs_attention'], fn($a, $b) => $a['score'] <=> $b['score'] );

		return $totals;
	}
}
