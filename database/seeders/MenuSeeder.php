<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $menus = [
            ['menu_title' => 'Home', 'parent_id' => 0, 'sort_order' => 0, 'slug' => '/'],
            ['menu_title' => 'Pages', 'parent_id' => 0, 'sort_order' => 1, 'slug' => '/pages'],
            ['menu_title' => 'Our Services', 'parent_id' => 2, 'sort_order' => 2, 'slug' => '/our-services'],
            ['menu_title' => 'About', 'parent_id' => 2, 'sort_order' => 3, 'slug' => '/about'],
            ['menu_title' => 'About Team', 'parent_id' => 4, 'sort_order' => 3, 'slug' => '/about-team'],
            ['menu_title' => 'About Clients', 'parent_id' => 4, 'sort_order' => 3, 'slug' => '/about-clients'],
            ['menu_title' => 'Contact Team', 'parent_id' => 5, 'sort_order' => 3, 'slug' => '/contact-team'],
            ['menu_title' => 'Contact Clients', 'parent_id' => 6, 'sort_order' => 3, 'slug' => '/contact-clients'],
            ['menu_title' => 'Contact', 'parent_id' => 2, 'sort_order' => 4, 'slug' => '/contact'],
            ['menu_title' => 'Portfolio', 'parent_id' => 2, 'sort_order' => 4, 'slug' => '/portfolio'],
            ['menu_title' => 'Gallery', 'parent_id' => 2, 'sort_order' => 4, 'slug' => '/gallery']
        ];
        foreach ($menus as $menu) {
            \App\Models\Menu::Create($menu);
        }
    }
}
