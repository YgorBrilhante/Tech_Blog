<?php

return array(
    'models' => array(
        // Tabelas Postgres
        'user' => array(
            'type'       => 'database',
            'connection' => 'default',
            'id'         => 'id',
            'table'      => 'users'
        ),

        'post' => array(
            'type'       => 'database',
            'connection' => 'default',
            'id'         => 'id',
            'table'      => 'posts'
        ),

        'tag' => array(
            'type'       => 'database',
            'connection' => 'default',
            'id'         => 'id',
            'table'      => 'tags'
        ),

        'comment' => array(
            'type'       => 'database',
            'connection' => 'default',
            'id'         => 'id',
            'table'      => 'comments'
        ),
    ),
    'relationships' => array(
        // user -> posts (um-para-muitos)
        array(
            'type'  => 'oneToMany',
            'owner' => 'user',
            'items' => 'post',
            'ownerOptions' => array(
                'itemsProperty' => 'posts'
            ),
            'itemsOptions' => array(
                'ownerProperty' => 'user',
                'ownerKey'      => 'user_id',
                // mantém comportamento consistente com FK ON DELETE CASCADE
                'onOwnerDelete' => 'delete'
            )
        ),

        // post -> comments (um-para-muitos)
        array(
            'type'  => 'oneToMany',
            'owner' => 'post',
            'items' => 'comment',
            'ownerOptions' => array(
                'itemsProperty' => 'comments'
            ),
            'itemsOptions' => array(
                'ownerProperty' => 'post',
                'ownerKey'      => 'post_id',
                'onOwnerDelete' => 'delete'
            )
        ),

        // user -> comments (um-para-muitos: autor dos comentários)
        array(
            'type'  => 'oneToMany',
            'owner' => 'user',
            'items' => 'comment',
            'ownerOptions' => array(
                'itemsProperty' => 'comments'
            ),
            'itemsOptions' => array(
                'ownerProperty' => 'user',
                'ownerKey'      => 'user_id',
                'onOwnerDelete' => 'delete'
            )
        ),

        // posts <-> tags (muitos-para-muitos via post_tags)
        array(
            'type'  => 'manyToMany',
            'left'  => 'post',
            'right' => 'tag',
            'leftOptions' => array(
                'property' => 'tags'
            ),
            'rightOptions' => array(
                'property' => 'posts'
            ),
            'pivot' => 'post_tags',
            'pivotOptions' => array(
                'connection' => 'default',
                'leftKey'    => 'post_id',
                'rightKey'   => 'tag_id'
            )
        )
    )    
);