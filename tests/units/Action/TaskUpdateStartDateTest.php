<?php

/*
 * This file is part of Hiject.
 *
 * Copyright (C) 2016 Hiject Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../Base.php';

use Hiject\Bus\Event\TaskEvent;
use Hiject\Model\TaskCreationModel;
use Hiject\Model\TaskFinderModel;
use Hiject\Model\ProjectModel;
use Hiject\Model\TaskModel;
use Hiject\Action\TaskUpdateStartDate;

class TaskUpdateStartDateTest extends Base
{
    public function testAction()
    {
        $projectModel = new ProjectModel($this->container);
        $taskCreationModel = new TaskCreationModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(array('name' => 'test1')));
        $this->assertEquals(1, $taskCreationModel->create(array('project_id' => 1, 'title' => 'test')));

        $event = new TaskEvent(array(
            'task_id' => 1,
            'task' => array(
                'project_id' => 1,
                'column_id' => 2,
            )
        ));

        $action = new TaskUpdateStartDate($this->container);
        $action->setProjectId(1);
        $action->setParam('column_id', 2);

        $this->assertTrue($action->execute($event, TaskModel::EVENT_MOVE_COLUMN));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);
        $this->assertEquals(time(), $task['date_started'], 'Date started delta', 2);
    }

    public function testWithWrongColumn()
    {
        $projectModel = new ProjectModel($this->container);
        $taskCreationModel = new TaskCreationModel($this->container);

        $this->assertEquals(1, $projectModel->create(array('name' => 'test1')));
        $this->assertEquals(1, $taskCreationModel->create(array('project_id' => 1, 'title' => 'test')));

        $event = new TaskEvent(array(
            'task_id' => 1,
            'task' => array(
                'project_id' => 1,
                'column_id' => 3,
            )
        ));

        $action = new TaskUpdateStartDate($this->container);
        $action->setProjectId(1);
        $action->setParam('column_id', 2);

        $this->assertFalse($action->execute($event, TaskModel::EVENT_MOVE_COLUMN));
    }
}
