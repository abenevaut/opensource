<?php

namespace abenevaut\Asana\Contracts;

enum AsanaDriversEnum: string
{
    case PROJECTS = 'Projects';
    case TASKS = 'Tasks';
    case USERS = 'Users';
    case WORKSPACES = 'Workspaces';
}
