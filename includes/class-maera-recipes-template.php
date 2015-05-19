<?php

class Maera_Recipes_Template {

    public function __construct() {

        add_filter( 'the_content', array( $this, 'single_recipe_content' ) );

    }

    public function single_recipe_content( $content ) {

        // No need to proceed any further if this in not a recipe.
        if ( ! is_singular( 'recipe' ) ) {
            return $content;
        }
        $settings     = get_option( 'maera_recipes' );
        $units_switch = ( isset( $settings['display_units_converter'] ) && $settings['display_units_converter'] ) ? $this->units_switch() : '';
        $template  = '<div class="recipe-wrapper">';
        $template .= $this->the_info();
        $template .= '<div class="recipe-flex-wrapper">';
        $template .= '<div class="ingredients-wrapper">' . $units_switch . $this->the_ingredients() . '</div>';
        $template .= '<div class="execution">' . $content . '</div>';
        $template .= '</div>';
        $template .= '</div>';

        return $template;

    }

    public function the_ingredients() {

        $units = array(
            'teaspoon'   => __( 'teaspoon', 'maera-recipes' ),
            'tablespoon' => __( 'tablespoon', 'maera-recipes' ),
            'cup'        => __( 'cup', 'maera-recipes' ),
            'us-gal'     => __( 'gallon (US)', 'maera-recipes' ),
            'us-quart'   => __( 'quart (US)', 'maera-recipes' ),
            'us-pint'    => __( 'pint (US)', 'maera-recipes' ),
            'us-oz'      => __( 'ounce (US)', 'maera-recipes' ),
            'imp-gal'    => __( 'gallon (Imperial/UK)', 'maera-recipes' ),
            'imp-quart'  => __( 'quart (Imperial/UK)', 'maera-recipes' ),
            'imp-pint'   => __( 'pint (Imperial/UK)', 'maera-recipes' ),
            'imp-oz'     => __( 'ounce (Imperial/UK)', 'maera-recipes' ),
            'ml'         => __( 'ml', 'maera-recipes' ),
            'lt'         => __( 'lt', 'maera-recipes' ),
            'pound'      => __( 'pound', 'maera-recipes' ),
            'ounce'      => __( 'ounce', 'maera-recipes' ),
            'gr'         => __( 'gram', 'maera-recipes' ),
            'kg'         => __( 'kg', 'maera-recipes' ),
            'mm'         => __( 'mm', 'maera-recipes' ),
            'cm'         => __( 'cm', 'maera-recipes' ),
            'm'          => __( 'm', 'maera-recipes' ),
            'inch'       => __( 'inch', 'maera-recipes' ),
        );

        if ( have_rows( 'ingredients' ) ) {

            $ingredients = '<ul>';
            while ( have_rows( 'ingredients' ) ) {

                the_row();
                $whole_fraction  = get_sub_field( 'whole_fraction' );
                $quantity        = get_sub_field( 'quantity' );
                $unit            = get_sub_field( 'unit' );
                $unit            = $this->unit_conversion( $quantity, $unit, 'unit' );
                $unit            = ( isset( $units[$unit] ) ) ? $units[$unit] : $unit;
                $ingredient      = get_term_by( 'id', get_sub_field( 'ingredient' ), 'ingredient' );
                $value           = $this->unit_conversion( $quantity, $unit, 'value' );

                $ingredients .= '<li>';
                $ingredients .= '<span class="quantity">' . $value . '<span class="unit">' . $unit . '</span></span>';
                $ingredients .= '<a href="' . get_term_link( $ingredient ) . '">' . $ingredient->name . '</a>';
                $ingredients .= '</li>';

            }

            $ingredients .= '</ul>';

        }

        return apply_filters( 'maera_recipes/template/ingredients', $ingredients );

    }

    /**
     * @var string  numeric or fraction value
     * @var string  the unit we're using
     * @var string  what we want to output. can be value/unit
     */
    public function unit_conversion( $value, $unit, $output ) {
        if ( is_singular( 'recipe' ) && isset( $_GET['mode'] ) ) {
            $mode = $_GET['mode'];

            /**
             * Do not proceed if this is not a valid mode.
             * Instead just return the default values.
             */
            if ( ! in_array( $mode, array( 'metric', 'imperial', 'us' ) ) ) {
                return ( 'value' == $output ) ? $value : $unit;
            }

            // Metric mode
            if ( 'metric' == $mode ) {

                switch ( $unit ) {
                    case 'us-gal' :
                        $value = round( $value * 3785.41 );
                        $unit  = 'ml';
                        break;
                    case 'us-quart' :
                        $value = round( $value * 946.353 );
                        $unit  = 'ml';
                        break;
                    case 'us-pint' :
                        $value = round( $value * 473.176 );
                        $unit  = 'ml';
                        break;
                    case 'us-floz' :
                        $value = round( $value * 29.5735 );
                        $unit  = 'ml';
                        break;
                    case 'imp-gal' :
                        $value = round( $value * 4546.09 );
                        $unit  = 'ml';
                        break;
                    case 'imp-quart' :
                        $value = round( $value * 1136.52 );
                        $unit  = 'ml';
                        break;
                    case 'imp-pint' :
                        $value = round( $value * 568.261 );
                        $unit  = 'ml';
                        break;
                    case 'imp-floz' :
                        $value = round( $value * 28.4131 );
                        $unit  = 'ml';
                        break;
                    case 'pound' :
                        $value = round( $value * 453.592 );
                        $unit  = 'gram';
                        break;
                    case 'ounce' :
                        $value = round( $value * 28.3495 );
                        $unit  = 'gram';
                        break;
                    case 'inch' :
                        $value = round( $value * 2.54, 1 );
                        $unit  = 'cm';
                        break;

                }

                // If the value is too large then round and change the unit
                if ( 1000 < $value ) {
                    if ( in_array( $unit, array( 'ml', 'gram' ) ) ) {
                        $value = round( $value / 1000, 2 );
                    }
                    $unit = 'ml' == $unit ? 'lt' : $unit;
                    $unit = 'gram' == $unit ? 'kg' : $unit;
                }

            // US mode
            } elseif ( 'us' == $mode ) {

                switch ( $unit ) {

                    case 'imp-gal' :
                        $value = round( $value * 1.20095, 2 );
                        $unit  = 'us-gal';
                        break;
                    case 'imp-quart' :
                        $value = round( $value * 1.20095, 2 );
                        $unit  = 'us-quart';
                        break;
                    case 'imp-pint' :
                        $value = round( $value * 1.20095, 2 );
                        $unit = 'us-pint';
                        break;
                    case 'imp-oz' :
                        $value = round( $value * 0.96076, 2 );
                        $unit  = 'us-oz';
                        break;
                    case 'ml' :
                        $value = round( $value * 0.033814, 2 );
                        $unit  = 'us-oz';
                        break;
                    case 'lt' :
                        $value = round( $value * 1.05669, 2 );
                        $unit  = 'us-quart';
                        break;
                    case 'gram' :
                        $value = round( $value * 0.035274, 2 );
                        $unit  = 'ounce';
                        break;
                    case 'kg' :
                        $value = round( $value * 2.20462, 2 );
                        $unit  = 'pound';
                        break;
                    case 'cm' :
                        $value = round( $value / 2.54 , 2 );
                        $unit  = 'inch';
                        break;
                    case 'm' :
                        $value = round( $value * 100 / 2.54 , 2 );
                        $unit  = 'inch';
                        break;

                }

            // Imperial mode
            } elseif ( 'imperial' == $mode ) {

                switch ( $unit ) {

                    case 'us-gal' :
                        $value = round( $value * 0.832674, 2 );
                        $unit  = 'imp-gal';
                        break;
                    case 'us-quart' :
                        $value = round( $value * 0.832674, 2 );
                        $unit  = 'imp-quart';
                        break;
                    case 'us-pint' :
                        $value = round( $value * 0.832674, 2 );
                        $unit  = 'imp-pint';
                        break;
                    case 'us-oz' :
                        $value = round( $value * 1.04084, 2 );
                        $unit  = 'imp-oz';
                        break;
                    case 'ml' :
                        $value = round( $value * 0.0351951, 2 );
                        $unit  = 'imp-oz';
                    case 'lt' :
                        $value = round( $value * 1.75975, 2 );
                        $unit  = 'imp-pint';
                        break;
                    case 'gram' :
                        $value = round( $value * 0.035274, 2 );
                        $unit  = 'ounce';
                        break;
                    case 'kg' :
                        $value = round( $value * 2.20462, 2 );
                        $unit  = 'pound';
                        break;
                    case 'cm' :
                        $value = round( $value / 2.54 , 2 );
                        $unit  = 'inch';
                        break;
                    case 'm' :
                        $value = round( $value * 100 / 2.54 , 2 );
                        $unit  = 'inch';
                        break;

                }

            }

        }

        return ( 'value' == $output ) ? $value : $unit;

    }

    public function the_info() {

        $servings  = get_field( 'servings' );
        $calories  = get_field( 'calories' );
        $prep_time = get_field( 'prep_time' );
        $cook_time = get_field( 'cook_time' );

        $template  = '<div class="recipe-info">';
        $template .= '<div class="servings">' . __( 'Servings:', 'maera-recipes' ) . '<span class="value">' . $servings . '</span></div>';
        if ( $calories ) {
            $template .= '<div class="calories">' . __( 'Calories:', 'maera-recipes' ) . '<span class="value">' . $calories . '</span>' . __( 'Kcal', 'maera-recipes' ) . '</div>';
        }
        if ( $prep_time ) {
            $template .= '<div class="prep_time">' . __( 'Prep. Time:', 'maera-recipes' ) . '<span class="value">' . $prep_time . '</span>' . __( 'minutes', 'maera-recipes' ) . '</div>';
        }
        if ( $cook_time ) {
            $template .= '<div class="prep_time">' . __( 'Cook Time:', 'maera-recipes' ) . '<span class="value">' . $cook_time . '</span>' . __( 'minutes', 'maera-recipes' ) . '</div>';
        }
        $template .= '</div>';

        return apply_filters( 'maera_recipes/template/info', $template );

    }

    public function units_switch() {

        $mode = isset( $_GET['mode'] ) ? ' ' . $_GET['mode'] : '';
        $template  = '<div class="units-switch' . $mode . '">';
        $template .= '<a href="?mode=metric" class="metric">' . __( 'Metric', 'maera-recipes' ) . '</a>';
        $template .= '<a href="?mode=imperial" class="imperial">' . __( 'Imp.', 'maera-recipes' ) . '</a>';
        $template .= '<a href="?mode=us" class="us">' . __( 'US', 'maera-recipes' ) . '</a>';
        $template .= '</div>';

        return $template;

    }

}
