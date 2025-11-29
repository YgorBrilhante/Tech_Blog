<?php

return array(
    'type'      => 'group',
    'defaults'  => array('action' => 'default'),
    'resolvers' => array(

        // ✅ Rotas da API
        'api' => array(
            'path' => 'api(/<action>(/<id>))',
            'defaults' => array('processor' => 'api')
        ),

        // ✅ Rota do exemplo padrão (greet)
        'greet' => array(
            'path' => 'greet(/<action>)',
            'defaults' => array('processor' => 'greet')
        ),

        // ✅ Rota de ação genérica (fallback interno)
        'action' => array(
            'path' => '<processor>/<action>'
        ),

        // ✅ Roteamento padrão (se nada for passado, vai para greet)
        'processor' => array(
            'path'     => '(<processor>)',
            'defaults' => array('processor' => 'greet')
        ),

        'Users' => array(
            'path' => 'users(/<action>(/<id>))',
            'defaults' => array('processor' => 'Users')
        ),

        'categorias' => array(
            'path' => 'categorias(/<action>(/<id>))',
            'defaults' => array('processor' => 'categorias')
        ),
        'Posts' => array(
            'path' => 'posts(/<action>(/<id>))',
            'defaults' => array('processor' => 'Posts')
        ),
        'Coments' => array(
            'path' => 'coments(/<action>(/<id>))',
            'defaults' => array('processor' => 'Coments')
        ),
        'Tags' => array(
            'path' => 'tags(/<action>(/<id>))',
            'defaults' => array('processor' => 'Tags')
        ),
        'PostsTags' => array(
            'path' => 'PostsTags(/<action>(/<id>))',
            'defaults' => array('processor' => 'PostsTags')
        ),
    )
);
