<?php


function json($data)
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function query($sql)
{
    $rows = $GLOBALS['db']->query($sql);
    return $rows ? $rows->fetchAll(PDO::FETCH_ASSOC) : [];
}

function response($success, $data = [])
{
    die(json_encode(array(
        'success' => $success,
        'data'    => $data
    ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function convertPostStatuses($items)
{
    $_items = [];
    foreach($items as $item) {
        $_items[$item['status_title']] = $item['post_count'];
    }
    return $_items;
}

function convertGroupStatuses($items)
{
    $_items = [];
    foreach($items as $item) {
        $_items[$item['status_title']] = $item['group_count'];
    }
    return $_items;
}

function setApiPostStatus($id, $status_id)
{
    $GLOBALS['db']->query('
       UPDATE 
           posts p 
       LEFT JOIN statuses s ON s.status_title = "' . $status_id . '"
       SET 
           p.status_id = s.id
       WHERE 
           p.id = ' . $id . ';
    ');
}

function setApiGroupStatus($id, $status_id)
{
    $GLOBALS['db']->query('
       UPDATE 
           groups g 
       LEFT JOIN statuses s ON s.status_title = "' . $status_id . '"
       SET 
           g.status_id = s.id
       WHERE 
           g.id = ' . $id . ';
    ');
}

function setGroupStatus($group_id)
{
    $GLOBALS['db']->query('
       UPDATE 
           groups g
       LEFT JOIN statuses s ON s.status_title = "rejected"
       SET 
           g.status_id = s.id
       WHERE 
           g.id = ' . $group_id . ';
    ');
}

function setPostEventDiscription($id, $event_discription, $image)
{
    if (!empty($image)) {
        $sql_imqge = 'p.event_image = "' . $image  . '"';
    } else {
        $sql_imqge = 'p.event_image = p.post_image';
    }

    $GLOBALS['db']->query('
       UPDATE 
           posts p 
       LEFT JOIN statuses s ON s.status_title = "approved"
       SET 
           p.event_discription = "' . $event_discription . '",
           p.status_id = s.id,
           ' . $sql_imqge . '
       WHERE 
           p.id = ' . $id . ';
    ');
}

function getPost($id)
{
    return query('
        SELECT 
            p.*,
            REPLACE(p.post_id, "post-", "") as post_id,
            IF(p.event_image IS NOT NULL, p.event_image, p.post_image) as post_image,
            g.group_id as id_group
        FROM 
            posts p 
            LEFT JOIN groups g ON g.id = p.group_id
        WHERE p.id = ' . $id . '
    ');
}