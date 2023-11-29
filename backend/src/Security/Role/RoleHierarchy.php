<?php

namespace App\Security\Role;

use Symfony\Component\Security\Core\Role\RoleHierarchy as BaseHierarchy;

class RoleHierarchy extends BaseHierarchy
{
    protected $tree;

    public function getMap()
    {
        if (null === $this->map) {
            $this->buildRoleMap();
        }

        return $this->map;
    }

    public function getTree()
    {
        if (null === $this->tree) {
            $this->tree = $this->buildTree();
        }

        return $this->tree;
    }

    protected function buildTree()
    {
        $tree = [];
        $roles = $this->getRoles();

        if (!$roles) {
            return $tree;
        }

        $uses = [];

        foreach ($roles as $role) {
            if (in_array($role, $uses)) {
                continue;
            }

            $tree[$role] = $this->buildRoleTree($role, $tree, $uses);
        }

        return $tree;
    }

    protected function buildRoleTree(string $role, array &$tree, array &$uses = [])
    {
        $map = $this->getMap();
        $roleTree = [];
        $children = $map[$role] ?? [];

        foreach ($children as $child) {
            if (in_array($child, $uses)) {
                continue;
            }

            $uses[] = $child;
            if (isset($tree[$child])) {
                $roleTree[$child] = $tree[$child];
                unset($tree[$child]);

            } else {
                $roleTree[$child] = $this->buildRoleTree($child, $tree, $uses);
            }

        }

        return $roleTree;
    }

    protected function getRoles()
    {
        $roles = [];
        $map = $this->getMap();

        foreach ($map as $role => $children) {
            $roles[] = $role;
            $roles = array_merge($roles, $children);
        }

        return array_unique($roles);
    }
}
