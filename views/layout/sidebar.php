<div class="app-sidebar">
    <div class="logo d-flex align-items-center justify-content-center">
        <a href="<?= home_url() ?>"><img src="<?= $base::assets('images/logo.svg') ?>" alt="Yuppins" /></a>
    </div>
    <div class="app-menu d-flex justify-content-between flex-column">
        <ul class="accordion-menu">
            <li class="sidebar-title">
                Gerenciamento
            </li>
            
            <?php 
            foreach($menu as $item) { 

                    $html  = '<li>';
                    $html .= '<a href="' . $base::url($item['link']) . '"><i class="material-icons-two-tone">' . $item['icon'] . '</i>' . $item['menu'] . '</a>';
                    $html .= '</li>';

                    echo $html; 
                
            } 
            ?>
            <li>
                <a href="<?=$base::url('integracoes')?>"><i class="material-icons-two-tone">link</i> Integrações</a>
            </li>
  
        </ul>

        <ul class="accordion-menu pb-3">
            <li>
                <a href="<?php echo wp_logout_url( home_url() ) ?>"><i style="transform: rotate(180deg);" class="material-icons-two-tone">exit_to_app</i>Desconectar</a>
            </li> 
        </ul>
    </div>
</div>