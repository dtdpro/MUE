<?php

/**
 * @copyright  2011-2013 Bronto Software, Inc.
 * @license http://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * 
 * @group object
 * @group account
 */
class Bronto_Tests_Api_Account_AccountTest extends Bronto_Tests_AbstractTest
{
    /**
     * @covers Bronto_Api_Account::readAll
     */
    public function testReadAllAccounts()
    {
        $rowset = $this->getObject()->readAll();

        $this->assertEquals(0, $rowset->count());
    }

    /**
     * @return Bronto_Api_Account
     */
    public function getObject()
    {
        return $this->getApi()->getAccountObject();
    }
}
