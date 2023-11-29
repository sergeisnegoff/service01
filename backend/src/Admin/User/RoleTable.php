<?php

namespace App\Admin\User;


use App\Security\Role\RoleHierarchy;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;
use Creonit\AdminBundle\Component\Scope\ListRowScope;
use Creonit\AdminBundle\Component\Scope\Scope;
use Creonit\AdminBundle\Component\TableComponent;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Contracts\Translation\TranslatorInterface;

class RoleTable extends TableComponent
{
    /**
     * @var RoleHierarchy
     */
    protected $roleHierarchy;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(RoleHierarchy $roleHierarchy, TranslatorInterface $translator)
    {
        $this->roleHierarchy = $roleHierarchy;
        $this->translator = $translator;
    }

    /**
     * @cols Разрешение, Код, .
     *
     * @action choose(options) {
     *  const $row = this.findRowById(options.rowId);
     *  const $btn = $row.find('button');
     *  const $icon = $btn.find('.fa');
     *  $row.toggleClass('success');
     *
     *  options.state = $row.hasClass('success');
     *  if (options.state) {
     *      $btn
     *          .removeClass('btn-default')
     *          .addClass('btn-danger');
     *
     *      $icon
     *          .removeClass('fa-star')
     *          .addClass('fa-ban');
     *  } else {
     *      $btn
     *          .removeClass('btn-danger')
     *          .addClass('btn-default');
     *
     *      $icon
     *          .removeClass('fa-ban')
     *          .addClass('fa-star');
     *  }
     *
     *  if (this.parent.actions.chooseRole) {
     *      this.parent.actions.chooseRole(options.key, options.state);
     *  }
     * }
     *
     * \Role
     * @relation parent > Role.role
     * @col {{ title | controls }}
     * @col {{ role }}
     * @col {{ button('', {size: 'xs', icon: (active ? 'ban' : 'star'), type: (active ? 'danger' : 'default')}) | action('choose', {key: _key, rowId: _row_id}) }}
     */
    public function schema()
    {
    }

    protected function getData(ComponentRequest $request, ComponentResponse $response, ListRowScope $scope, $relation = null, $relationValue = null, $level = 0)
    {
        $mask = $this->getMask($scope, $relation, $relationValue);
        $entities = [];

        $data = $this->getRoles($request, $response, $relation, $relationValue);

        foreach ($data as $role) {
            $entityData = new ParameterBag([
                '_key' => $role,
                'role' => $role,
                'title' => $this->translator->trans($role, [], 'roles'),
                'level' => $level,
            ]);

            $this->decorate($request, $response, $entityData, $role, $scope, $relation, $relationValue, $level);
            $entities[] = $entityData;
        }

        if ($entities) {
            foreach ($this->findRelations(null, $scope) as $rel) {
                foreach ($entities as $entityData) {
                    $this->getData($request, $response, $rel->getSourceScope(), $rel, $entityData->get($rel->getTargetField()->getName()), $level + 1);
                }
            }
        }

        $this->pushDataEntities($response, $mask, $entities);
    }

    protected function getRoles(ComponentRequest $request, ComponentResponse $response, $relation = null, $relationValue = null)
    {
        $tree = $this->roleHierarchy->getTree();

        return $this->findRoles($tree, $relationValue);
    }

    protected function findRoles(array $tree, string $parent = null)
    {
        if (null === $parent) {
            return array_keys($tree);
        }

        foreach ($tree as $main => $roles) {
            if ($main === $parent) {
                return array_keys($roles);
            }

            if ($childTree = $this->findRoles($roles, $parent)) {
                return array_values($childTree);
            }
        }

        return [];
    }

    protected function decorate(ComponentRequest $request, ComponentResponse $response, ParameterBag $data, $entity, Scope $scope, $relation, $relationValue, $level)
    {
        if (in_array($entity, $request->query->get('actives', []))) {
            $data->add([
                'active' => true,
                '_row_class' => 'success',
            ]);
        }
    }
}
