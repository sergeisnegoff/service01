<?php

namespace App\Admin\User;


use App\Model\Map\UserGroupRelTableMap;
use App\Model\UserGroup;
use App\Model\UserGroupRel;
use App\Model\UserGroupRelQuery;
use Creonit\AdminBundle\Component\ChooseTableComponent;
use Creonit\AdminBundle\Component\Request\ComponentRequest;
use Creonit\AdminBundle\Component\Response\ComponentResponse;

class ChooseUserGroupTable extends ChooseTableComponent
{
    /**
     * @title Группы пользователя
     * @scope UserGroup
     *
     * @cols Группа
     *
     * \UserGroup
     * @col {{ title | action('choose', {key: _key, rowId: _row_id}) }}
     */
    public function schema()
    {
        parent::schema();

        $this->setAction('choose', "function(options) {
            const \$row = this.findRowById(options.rowId);

            this.request('choose', $.extend({key: options.key}, this.getQuery()), {state: !\$row.hasClass('success')}, function(response) {
                if (this.checkResponse(response)) {
                    \$row.toggleClass('success');
                    if(this.query.reload && this.parent) {
                        this.parent.loadData();
                    }
                }
            });
        }");
    }

    /**
     * @param ComponentRequest $request
     * @param ComponentResponse $response
     * @param UserGroup $target
     * @param bool $state
     *
     * @return mixed|void
     */
    protected function choose(ComponentRequest $request, ComponentResponse $response, $target, $state)
    {
        $rel = UserGroupRelQuery::create()->filterByUserId($request->query->getInt('user_id'))->filterByUserGroup($target)->findOne() ?: new UserGroupRel();

        if ($rel->isNew()) {
            $rel
                ->setUserId($request->query->getInt('user_id'))
                ->setUserGroup($target)
                ->save();

        } else {
            $rel->delete();
        }
    }

    protected function actives(ComponentRequest $request, ComponentResponse $response)
    {
        return UserGroupRelQuery::create()
            ->filterByUserId($request->query->getInt('user_id'))
            ->select(UserGroupRelTableMap::COL_USER_GROUP_ID)
            ->find()
            ->getData();
    }
}
