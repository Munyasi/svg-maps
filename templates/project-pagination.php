<ul id="wbb_pagination">
    <?php
    if( $paged > 3 && $num_pag > 5 )
    {
        echo "<li class='' page='1'>|<</li>";
        echo "<li class='' page='".intval($paged-1)."'><</li>";
    }
    
    if( $num_pag <= 5 )
    {
        
        $items = $num_pag;
        
        for($x = 1; $x <= $items ; $x++ )
        {

            if( intval($x) === intval($paged) )
                $active = "active";
            else
                $active = "";

            echo "<li class='$active' page='$x'>$x</li>";

        }
        
    }
    else
    {
        if( ($paged+2) >= $num_pag )
            $page_init = $num_pag-4;
        else
            $page_init = ($paged-2);
        
        for($p = $page_init ; $p < $paged; $p ++)
        {
            if( $p > 0 )
                echo "<li class='' page='$p'>$p</li>";
        }
        
        echo "<li class='active' page='$paged'>$paged</li>";
        
        if( $paged < $num_pag )
        {
            if( ($paged+2) < 5 )
                $page_limit = 5;
            else
                $page_limit = ($paged+2);
                
            for($n = ($paged+1) ; $n <= $page_limit; $n ++)
            {
                
                if( $n <= $num_pag )
                    echo "<li class='' page='$n'>$n</li>";
            }
            
        }
        
    }
    
    
    if( $num_pag > 5 && $paged <= ($num_pag-3) )
    {
        echo "<li class='' page='".intval($paged+1)."'>></li>";
        echo "<li class='' page='$num_pag'>>|</li>";
    }
    
    ?>
</ul>