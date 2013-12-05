<?php 
//Single template for sketchbook CPT; generally not directly accessed

get_header(); ?>


<!-- Row for main content area -->
	<div class="large-12 columns" role="main">		
		
		<?php /* Start loop */ ?>
		

		<?php 
            $args = array(
              'post_type' => 'tn_cstm_sketchbook',
              'orderby' => 'menu_order',
              'order' =>  'ASC'
              //'nopaging' => true,
              );

              $sketches = new WP_Query($args);
              if($sketches->have_posts()) : 
              while($sketches->have_posts()) : 
                 $sketches->the_post();

               if(get_field('sketch')) :
                          $sketch = get_field('sketch');
                          $size  = "full";
                          $large_image = wp_get_attachment_image_src( $sketch, $size );
                          $attachment = get_post( get_field('sketch') );
                          $alt = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
               endif;
              
              ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				
					<section id="work-slides" class="">

							<?php 
							<img src="<?php echo $large_image[0]; ?>" alt="<?php echo $alt; ?>" />
							?>		
					</section>		
			</article>

		<?php endwhile; 
				endif; // End the loop ?>

		


	</div>

		
<?php get_footer(); ?>
