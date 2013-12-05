<?php
/*
Template Name: Work Archive Template
*/
get_header(); ?>


  <!-- Secondary Nav Bar -->

 <?php tn_cstm_portfolio_menu(); ?>

<!-- End Secondary Nav Bar -->

<!-- Row for main content area -->
  <div class="small-12 large-9 columns" role="main" >
        
    <section id="featured" class="row" data-magellan-destination='featured'>

      <h3>Featured</h3>
      <hr>  

        <div id="featured-work">
              <ul data-orbit="" data-options="bullets:false;stack_on_small: true;" class="orbit-slides-container small-12 columns">


                  <?php
                  $args = array(
                  'orderby'   => 'menu_order',
                  'order'   => 'ASC',
                  'post_type' => 'tn_cstm_portfolio',
                  'meta_query' => array(
                    array(
                      'key' => 'featured_work',
                      'value' => '"yes"',
                      'compare' => 'LIKE'
                      )
                    )
                  );
                 $query = new WP_Query( $args );
                 while ($query->have_posts()) : $query->the_post();
                 ?>

                <li>
                  <div class="work-slide-container large-12 columns">
                    <div class="large-6 small-12 columns">
                       <a href="<? the_permalink()?>" rel="bookmark" title="<?php the_title(); ?>">
                      <?php 
                        tn_cstm_thumbnail_display(); 
                     
                        ?>
                      </a>
                    </div>
                    <div class="large-6 columns">
                      <h4>
                      <a href="<? the_permalink()?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a>
                      </h4>
                      <p> <?php 
                        $description = get_field('featured_excerpt');
                        if($description) { 
                          the_field('featured_excerpt');
                        } ?>
                      </p>
                      <h5><a href="<? the_permalink()?>" rel="bookmark" title="<?php the_title(); ?>">Learn More</a></h5>
                    </div>
                  </div>
                </li>
                <?php endwhile; wp_reset_query(); ?>

              </ul>
          </div>
      <hr>
    </section>

<!-- Begin custom tax loop -->
  <?php

    $categories = get_terms('tn_cstm_work_taxonomy','parent=0&order=DESC');
    foreach ( $categories as $category ) : 
    ?>
      <div class="row">
        <section id="<?php echo $category->slug; ?>" class="large-12 columns" data-magellan-destination='<?php echo $category->slug; ?>'> 
           <h3><?php echo $category->name; ?></h3>

           <ul class="large-block-grid-4 small-block-grid-2">
            <?php 
            $posts = get_posts(array(
              'post_type' => 'tn_cstm_portfolio',
              'orderby' => 'menu_order',
              'order' =>  'ASC',
              'taxonomy' => $category->taxonomy,
              'term'  => $category->slug,
              'nopaging' => true,
              ));
            
            foreach($posts as $post) :
              setup_postdata($post);
            ?>

              <li>
                  <?php 
                         if(function_exists('tn_cstm_work_thumb')) {
                          tn_cstm_work_thumb(); 
                        
                        }
                    
                    ?>
              </li>
            
            <?php endforeach; ?>
      
          </ul>
        </section>
      </div><!-- .row -->     

  <?php endforeach; ?>

  <!-- EOF nested custom tax loop -->
  
  </div>  <!-- End Content -->



<?php get_footer(); ?>