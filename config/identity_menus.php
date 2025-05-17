<?php

return [
    // Propietarios
    'tipo A' => [
        ['label' => 'Guest List', 'route' => 'invitations.sent'],
        ['label' => 'Dashboard', 'route' => 'type-a.dashboard'],
        ['label' => 'Invite User', 'route' => 'invitations.create'],
    ],
    'tipo B' => [
        ['label' => 'Guest List', 'route' => 'invitations.sent'],
        ['label' => 'Dashboard', 'route' => 'type-b.dashboard'],
        ['label' => 'Invite User', 'route' => 'invitations.create'],
        ['label' => 'New Post', 'route' => '#'],
    ],
    'tipo C' => [
        ['label' => 'Guest List', 'route' => 'invitations.sent'],
        ['label' => 'Dashboard', 'route' => 'type-c.dashboard'],
        ['label' => 'Invite User', 'route' => 'invitations.create'],
    ],
    'tipo D' => [
        ['label' => 'Guest List', 'route' => 'type-d.list'],
        ['label' => 'Dashboard', 'route' => 'type-d.dashboard'],
    ],
    'tipo E' => [
        ['label' => 'Guest List', 'route' => 'type-e.list'],
        ['label' => 'Dashboard', 'route' => 'type-e.dashboard'],
    ],
    'tipo F' => [
        ['label' => 'Guest List', 'route' => 'type-f.list'],
        ['label' => 'Dashboard', 'route' => 'type-f.dashboard'],
    ],
    'tipo G' => [
        ['label' => 'Guest List', 'route' => 'type-g.list'],
        ['label' => 'Dashboard', 'route' => 'type-g.dashboard'],
    ],
    'tipo H' => [
        ['label' => 'Guest List', 'route' => 'type-h.list'],
        ['label' => 'Dashboard', 'route' => 'type-h.dashboard'],
    ],

    // Invitados nivel 1 y 2
    'tipo A1' => [
        ['label' => 'Guest List', 'route' => 'invitations.sent'],
        ['label' => 'Dashboard', 'route' => 'type-a.dashboard'],
    ],
    'tipo A2' => [
        ['label' => 'Dash tipo A2', 'route' => 'type-a.dashboard'],
        // Sin Guest List, por ejemplo, para diferenciar
    ],
    'tipo B1' => [
        ['label' => 'Guest List', 'route' => 'invitations.sent'],
        ['label' => 'Dashboard', 'route' => 'type-b.dashboard'],
        ['label' => 'New Post', 'route' => '#'],
    ],
    'tipo B2' => [
        ['label' => 'Dash tipo B2', 'route' => 'type-b.dashboard'],
        // Sin Guest List ni New Post, por ejemplo
    ],
    'tipo C1' => [
        ['label' => 'Guest List', 'route' => 'invitations.sent'],
        ['label' => 'Dashboard', 'route' => 'type-c.dashboard'],
    ],
    'tipo C2' => [
        ['label' => 'Dash de C2', 'route' => 'type-c.dashboard'],
    ],
];