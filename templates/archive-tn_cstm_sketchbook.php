<?php
/*
Template Name: Sketchbook Pages 
*/

get_header(); ?>


<!-- Row for main content area -->
  <div class="full-width" role="main" >
        
    <section id="sketchbook-wall">


       <ul id="sketchbook-container" class="large-block-grid-4">
            <?php 

            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
            $args = array(
              'post_type' => 'tn_cstm_sketchbook',
              'orderby' => 'date',
              'order' =>  'DESC',
              'posts_per_page' => 8,
              'paged'=> $paged 
              );

              $loop = new WP_Query($args);
              while($loop->have_posts()) : $loop->the_post(); ?>
              
              <?php 
                
                if( has_post_thumbnail() ) :
                  $sketch = get_post_thumbnail_id($post->ID);     
                  $large_image = get_attachment_link( $sketch );
                   ?>

                  <li class="sketch-leaf">
                    <figure class="sketch-thumb">
    
                      <a href="<?php echo $large_image; ?>" data-id="<?php the_ID(); ?>"  class="reveal" >
                      <?php echo get_the_post_thumbnail($post->ID, 'medium-sketch'); ?>
                      </a> 
                      <figcaption>
        
                      </figcaption>

                      </figure>
                     
                  </li>
                <?php endif; ?>


            <?php endwhile; ?>
      
          </ul>

      <div class="row">

        <div class="large-12 columns"> 

          <nav id="sketchbook-nav" class="small-3 small-centered columns">
            <?php
            // Bring $wp_query into the scope of the function
            global $wp_query;

            // Backup the original property value
            $backup_page_total = $wp_query->max_num_pages;

            // Copy the custom query property to the $wp_query object
            $wp_query->max_num_pages = $loop->max_num_pages;
            ?>

            <!-- now show the paging links -->
            <div class="next-leaf alignleft"><?php next_posts_link('Next Entries'); ?></div>
            <div class="previous-leaf alignright"><?php previous_posts_link('Previous Entries'); ?></div>

            <?php
            // Finally restore the $wp_query property to it's original value
            $wp_query->max_num_pages = $backup_page_total;
            ?>
          </nav>
        </div>
      </div>
      
    </section>

  


  
  </div>  <!-- End Content -->


<?php get_footer(); ?>
