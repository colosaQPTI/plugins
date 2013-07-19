<?php

require_once 'propel/om/BaseObject.php';

require_once 'propel/om/Persistent.php';


include_once 'propel/util/Criteria.php';

include_once 'classes/model/AppSlaPeer.php';

/**
 * Base class that represents a row from the 'APP_SLA' table.
 *
 * 
 *
 * @package    workflow.classes.model.om
 */
abstract class BaseAppSla extends BaseObject implements Persistent
{

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        AppSlaPeer
    */
    protected static $peer;

    /**
     * The value for the app_uid field.
     * @var        string
     */
    protected $app_uid = '';

    /**
     * The value for the sla_uid field.
     * @var        string
     */
    protected $sla_uid = '';

    /**
     * The value for the app_sla_init_date field.
     * @var        int
     */
    protected $app_sla_init_date;

    /**
     * The value for the app_sla_due_date field.
     * @var        int
     */
    protected $app_sla_due_date;

    /**
     * The value for the app_sla_finish_date field.
     * @var        int
     */
    protected $app_sla_finish_date;

    /**
     * The value for the app_sla_duration field.
     * @var        double
     */
    protected $app_sla_duration = 0;

    /**
     * The value for the app_sla_remaining field.
     * @var        double
     */
    protected $app_sla_remaining = 0;

    /**
     * The value for the app_sla_exceeded field.
     * @var        double
     */
    protected $app_sla_exceeded = 0;

    /**
     * The value for the app_sla_pen_value field.
     * @var        double
     */
    protected $app_sla_pen_value = 0;

    /**
     * The value for the app_sla_status field.
     * @var        string
     */
    protected $app_sla_status = '';

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInSave = false;

    /**
     * Flag to prevent endless validation loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInValidation = false;

    /**
     * Get the [app_uid] column value.
     * 
     * @return     string
     */
    public function getAppUid()
    {

        return $this->app_uid;
    }

    /**
     * Get the [sla_uid] column value.
     * 
     * @return     string
     */
    public function getSlaUid()
    {

        return $this->sla_uid;
    }

    /**
     * Get the [optionally formatted] [app_sla_init_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getAppSlaInitDate($format = 'Y-m-d H:i:s')
    {

        if ($this->app_sla_init_date === null || $this->app_sla_init_date === '') {
            return null;
        } elseif (!is_int($this->app_sla_init_date)) {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime($this->app_sla_init_date);
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse value of [app_sla_init_date] as date/time value: " .
                    var_export($this->app_sla_init_date, true));
            }
        } else {
            $ts = $this->app_sla_init_date;
        }
        if ($format === null) {
            return $ts;
        } elseif (strpos($format, '%') !== false) {
            return strftime($format, $ts);
        } else {
            return date($format, $ts);
        }
    }

    /**
     * Get the [optionally formatted] [app_sla_due_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getAppSlaDueDate($format = 'Y-m-d H:i:s')
    {

        if ($this->app_sla_due_date === null || $this->app_sla_due_date === '') {
            return null;
        } elseif (!is_int($this->app_sla_due_date)) {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime($this->app_sla_due_date);
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse value of [app_sla_due_date] as date/time value: " .
                    var_export($this->app_sla_due_date, true));
            }
        } else {
            $ts = $this->app_sla_due_date;
        }
        if ($format === null) {
            return $ts;
        } elseif (strpos($format, '%') !== false) {
            return strftime($format, $ts);
        } else {
            return date($format, $ts);
        }
    }

    /**
     * Get the [optionally formatted] [app_sla_finish_date] column value.
     * 
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                          If format is NULL, then the integer unix timestamp will be returned.
     * @return     mixed Formatted date/time value as string or integer unix timestamp (if format is NULL).
     * @throws     PropelException - if unable to convert the date/time to timestamp.
     */
    public function getAppSlaFinishDate($format = 'Y-m-d H:i:s')
    {

        if ($this->app_sla_finish_date === null || $this->app_sla_finish_date === '') {
            return null;
        } elseif (!is_int($this->app_sla_finish_date)) {
            // a non-timestamp value was set externally, so we convert it
            $ts = strtotime($this->app_sla_finish_date);
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse value of [app_sla_finish_date] as date/time value: " .
                    var_export($this->app_sla_finish_date, true));
            }
        } else {
            $ts = $this->app_sla_finish_date;
        }
        if ($format === null) {
            return $ts;
        } elseif (strpos($format, '%') !== false) {
            return strftime($format, $ts);
        } else {
            return date($format, $ts);
        }
    }

    /**
     * Get the [app_sla_duration] column value.
     * 
     * @return     double
     */
    public function getAppSlaDuration()
    {

        return $this->app_sla_duration;
    }

    /**
     * Get the [app_sla_remaining] column value.
     * 
     * @return     double
     */
    public function getAppSlaRemaining()
    {

        return $this->app_sla_remaining;
    }

    /**
     * Get the [app_sla_exceeded] column value.
     * 
     * @return     double
     */
    public function getAppSlaExceeded()
    {

        return $this->app_sla_exceeded;
    }

    /**
     * Get the [app_sla_pen_value] column value.
     * 
     * @return     double
     */
    public function getAppSlaPenValue()
    {

        return $this->app_sla_pen_value;
    }

    /**
     * Get the [app_sla_status] column value.
     * 
     * @return     string
     */
    public function getAppSlaStatus()
    {

        return $this->app_sla_status;
    }

    /**
     * Set the value of [app_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppUid($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->app_uid !== $v || $v === '') {
            $this->app_uid = $v;
            $this->modifiedColumns[] = AppSlaPeer::APP_UID;
        }

    } // setAppUid()

    /**
     * Set the value of [sla_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setSlaUid($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->sla_uid !== $v || $v === '') {
            $this->sla_uid = $v;
            $this->modifiedColumns[] = AppSlaPeer::SLA_UID;
        }

    } // setSlaUid()

    /**
     * Set the value of [app_sla_init_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAppSlaInitDate($v)
    {

        if ($v !== null && !is_int($v)) {
            $ts = strtotime($v);
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse date/time value for [app_sla_init_date] from input: " .
                    var_export($v, true));
            }
        } else {
            $ts = $v;
        }
        if ($this->app_sla_init_date !== $ts) {
            $this->app_sla_init_date = $ts;
            $this->modifiedColumns[] = AppSlaPeer::APP_SLA_INIT_DATE;
        }

    } // setAppSlaInitDate()

    /**
     * Set the value of [app_sla_due_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAppSlaDueDate($v)
    {

        if ($v !== null && !is_int($v)) {
            $ts = strtotime($v);
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse date/time value for [app_sla_due_date] from input: " .
                    var_export($v, true));
            }
        } else {
            $ts = $v;
        }
        if ($this->app_sla_due_date !== $ts) {
            $this->app_sla_due_date = $ts;
            $this->modifiedColumns[] = AppSlaPeer::APP_SLA_DUE_DATE;
        }

    } // setAppSlaDueDate()

    /**
     * Set the value of [app_sla_finish_date] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setAppSlaFinishDate($v)
    {

        if ($v !== null && !is_int($v)) {
            $ts = strtotime($v);
            if ($ts === -1 || $ts === false) {
                throw new PropelException("Unable to parse date/time value for [app_sla_finish_date] from input: " .
                    var_export($v, true));
            }
        } else {
            $ts = $v;
        }
        if ($this->app_sla_finish_date !== $ts) {
            $this->app_sla_finish_date = $ts;
            $this->modifiedColumns[] = AppSlaPeer::APP_SLA_FINISH_DATE;
        }

    } // setAppSlaFinishDate()

    /**
     * Set the value of [app_sla_duration] column.
     * 
     * @param      double $v new value
     * @return     void
     */
    public function setAppSlaDuration($v)
    {

        if ($this->app_sla_duration !== $v || $v === 0) {
            $this->app_sla_duration = $v;
            $this->modifiedColumns[] = AppSlaPeer::APP_SLA_DURATION;
        }

    } // setAppSlaDuration()

    /**
     * Set the value of [app_sla_remaining] column.
     * 
     * @param      double $v new value
     * @return     void
     */
    public function setAppSlaRemaining($v)
    {

        if ($this->app_sla_remaining !== $v || $v === 0) {
            $this->app_sla_remaining = $v;
            $this->modifiedColumns[] = AppSlaPeer::APP_SLA_REMAINING;
        }

    } // setAppSlaRemaining()

    /**
     * Set the value of [app_sla_exceeded] column.
     * 
     * @param      double $v new value
     * @return     void
     */
    public function setAppSlaExceeded($v)
    {

        if ($this->app_sla_exceeded !== $v || $v === 0) {
            $this->app_sla_exceeded = $v;
            $this->modifiedColumns[] = AppSlaPeer::APP_SLA_EXCEEDED;
        }

    } // setAppSlaExceeded()

    /**
     * Set the value of [app_sla_pen_value] column.
     * 
     * @param      double $v new value
     * @return     void
     */
    public function setAppSlaPenValue($v)
    {

        if ($this->app_sla_pen_value !== $v || $v === 0) {
            $this->app_sla_pen_value = $v;
            $this->modifiedColumns[] = AppSlaPeer::APP_SLA_PEN_VALUE;
        }

    } // setAppSlaPenValue()

    /**
     * Set the value of [app_sla_status] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setAppSlaStatus($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->app_sla_status !== $v || $v === '') {
            $this->app_sla_status = $v;
            $this->modifiedColumns[] = AppSlaPeer::APP_SLA_STATUS;
        }

    } // setAppSlaStatus()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (1-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param      ResultSet $rs The ResultSet class with cursor advanced to desired record pos.
     * @param      int $startcol 1-based offset column which indicates which restultset column to start with.
     * @return     int next starting column
     * @throws     PropelException  - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate(ResultSet $rs, $startcol = 1)
    {
        try {

            $this->app_uid = $rs->getString($startcol + 0);

            $this->sla_uid = $rs->getString($startcol + 1);

            $this->app_sla_init_date = $rs->getTimestamp($startcol + 2, null);

            $this->app_sla_due_date = $rs->getTimestamp($startcol + 3, null);

            $this->app_sla_finish_date = $rs->getTimestamp($startcol + 4, null);

            $this->app_sla_duration = $rs->getFloat($startcol + 5);

            $this->app_sla_remaining = $rs->getFloat($startcol + 6);

            $this->app_sla_exceeded = $rs->getFloat($startcol + 7);

            $this->app_sla_pen_value = $rs->getFloat($startcol + 8);

            $this->app_sla_status = $rs->getString($startcol + 9);

            $this->resetModified();

            $this->setNew(false);

            // FIXME - using NUM_COLUMNS may be clearer.
            return $startcol + 10; // 10 = AppSlaPeer::NUM_COLUMNS - AppSlaPeer::NUM_LAZY_LOAD_COLUMNS).

        } catch (Exception $e) {
            throw new PropelException("Error populating AppSla object", $e);
        }
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      Connection $con
     * @return     void
     * @throws     PropelException
     * @see        BaseObject::setDeleted()
     * @see        BaseObject::isDeleted()
     */
    public function delete($con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getConnection(AppSlaPeer::DATABASE_NAME);
        }

        try {
            $con->begin();
            AppSlaPeer::doDelete($this, $con);
            $this->setDeleted(true);
            $con->commit();
        } catch (PropelException $e) {
            $con->rollback();
            throw $e;
        }
    }

    /**
     * Stores the object in the database.  If the object is new,
     * it inserts it; otherwise an update is performed.  This method
     * wraps the doSave() worker method in a transaction.
     *
     * @param      Connection $con
     * @return     int The number of rows affected by this insert/update
     * @throws     PropelException
     * @see        doSave()
     */
    public function save($con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($con === null) {
            $con = Propel::getConnection(AppSlaPeer::DATABASE_NAME);
        }

        try {
            $con->begin();
            $affectedRows = $this->doSave($con);
            $con->commit();
            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollback();
            throw $e;
        }
    }

    /**
     * Stores the object in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      Connection $con
     * @return     int The number of rows affected by this insert/update and any referring
     * @throws     PropelException
     * @see        save()
     */
    protected function doSave($con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;


            // If this object has been modified, then save it to the database.
            if ($this->isModified()) {
                if ($this->isNew()) {
                    $pk = AppSlaPeer::doInsert($this, $con);
                    $affectedRows += 1; // we are assuming that there is only 1 row per doInsert() which
                                         // should always be true here (even though technically
                                         // BasePeer::doInsert() can insert multiple rows).

                    $this->setNew(false);
                } else {
                    $affectedRows += AppSlaPeer::doUpdate($this, $con);
                }
                $this->resetModified(); // [HL] After being saved an object is no longer 'modified'
            }

            $this->alreadyInSave = false;
        }
        return $affectedRows;
    } // doSave()

    /**
     * Array of ValidationFailed objects.
     * @var        array ValidationFailed[]
     */
    protected $validationFailures = array();

    /**
     * Gets any ValidationFailed objects that resulted from last call to validate().
     *
     *
     * @return     array ValidationFailed[]
     * @see        validate()
     */
    public function getValidationFailures()
    {
        return $this->validationFailures;
    }

    /**
     * Validates the objects modified field values and all objects related to this table.
     *
     * If $columns is either a column name or an array of column names
     * only those columns are validated.
     *
     * @param      mixed $columns Column name or an array of column names.
     * @return     boolean Whether all columns pass validation.
     * @see        doValidate()
     * @see        getValidationFailures()
     */
    public function validate($columns = null)
    {
        $res = $this->doValidate($columns);
        if ($res === true) {
            $this->validationFailures = array();
            return true;
        } else {
            $this->validationFailures = $res;
            return false;
        }
    }

    /**
     * This function performs the validation work for complex object models.
     *
     * In addition to checking the current object, all related objects will
     * also be validated.  If all pass then <code>true</code> is returned; otherwise
     * an aggreagated array of ValidationFailed objects will be returned.
     *
     * @param      array $columns Array of column names to validate.
     * @return     mixed <code>true</code> if all validations pass; 
                   array of <code>ValidationFailed</code> objects otherwise.
     */
    protected function doValidate($columns = null)
    {
        if (!$this->alreadyInValidation) {
            $this->alreadyInValidation = true;
            $retval = null;

            $failureMap = array();


            if (($retval = AppSlaPeer::doValidate($this, $columns)) !== true) {
                $failureMap = array_merge($failureMap, $retval);
            }



            $this->alreadyInValidation = false;
        }

        return (!empty($failureMap) ? $failureMap : true);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TYPE_PHPNAME,
     *                     TYPE_COLNAME, TYPE_FIELDNAME, TYPE_NUM
     * @return     mixed Value of field.
     */
    public function getByName($name, $type = BasePeer::TYPE_PHPNAME)
    {
        $pos = AppSlaPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
        return $this->getByPosition($pos);
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return     mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch($pos) {
            case 0:
                return $this->getAppUid();
                break;
            case 1:
                return $this->getSlaUid();
                break;
            case 2:
                return $this->getAppSlaInitDate();
                break;
            case 3:
                return $this->getAppSlaDueDate();
                break;
            case 4:
                return $this->getAppSlaFinishDate();
                break;
            case 5:
                return $this->getAppSlaDuration();
                break;
            case 6:
                return $this->getAppSlaRemaining();
                break;
            case 7:
                return $this->getAppSlaExceeded();
                break;
            case 8:
                return $this->getAppSlaPenValue();
                break;
            case 9:
                return $this->getAppSlaStatus();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param      string $keyType One of the class type constants TYPE_PHPNAME,
     *                        TYPE_COLNAME, TYPE_FIELDNAME, TYPE_NUM
     * @return     an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = BasePeer::TYPE_PHPNAME)
    {
        $keys = AppSlaPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getAppUid(),
            $keys[1] => $this->getSlaUid(),
            $keys[2] => $this->getAppSlaInitDate(),
            $keys[3] => $this->getAppSlaDueDate(),
            $keys[4] => $this->getAppSlaFinishDate(),
            $keys[5] => $this->getAppSlaDuration(),
            $keys[6] => $this->getAppSlaRemaining(),
            $keys[7] => $this->getAppSlaExceeded(),
            $keys[8] => $this->getAppSlaPenValue(),
            $keys[9] => $this->getAppSlaStatus(),
        );
        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param      string $name peer name
     * @param      mixed $value field value
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TYPE_PHPNAME,
     *                     TYPE_COLNAME, TYPE_FIELDNAME, TYPE_NUM
     * @return     void
     */
    public function setByName($name, $value, $type = BasePeer::TYPE_PHPNAME)
    {
        $pos = AppSlaPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @param      mixed $value field value
     * @return     void
     */
    public function setByPosition($pos, $value)
    {
        switch($pos) {
            case 0:
                $this->setAppUid($value);
                break;
            case 1:
                $this->setSlaUid($value);
                break;
            case 2:
                $this->setAppSlaInitDate($value);
                break;
            case 3:
                $this->setAppSlaDueDate($value);
                break;
            case 4:
                $this->setAppSlaFinishDate($value);
                break;
            case 5:
                $this->setAppSlaDuration($value);
                break;
            case 6:
                $this->setAppSlaRemaining($value);
                break;
            case 7:
                $this->setAppSlaExceeded($value);
                break;
            case 8:
                $this->setAppSlaPenValue($value);
                break;
            case 9:
                $this->setAppSlaStatus($value);
                break;
        } // switch()
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TYPE_PHPNAME, TYPE_COLNAME, TYPE_FIELDNAME,
     * TYPE_NUM. The default key type is the column's phpname (e.g. 'authorId')
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return     void
     */
    public function fromArray($arr, $keyType = BasePeer::TYPE_PHPNAME)
    {
        $keys = AppSlaPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setAppUid($arr[$keys[0]]);
        }

        if (array_key_exists($keys[1], $arr)) {
            $this->setSlaUid($arr[$keys[1]]);
        }

        if (array_key_exists($keys[2], $arr)) {
            $this->setAppSlaInitDate($arr[$keys[2]]);
        }

        if (array_key_exists($keys[3], $arr)) {
            $this->setAppSlaDueDate($arr[$keys[3]]);
        }

        if (array_key_exists($keys[4], $arr)) {
            $this->setAppSlaFinishDate($arr[$keys[4]]);
        }

        if (array_key_exists($keys[5], $arr)) {
            $this->setAppSlaDuration($arr[$keys[5]]);
        }

        if (array_key_exists($keys[6], $arr)) {
            $this->setAppSlaRemaining($arr[$keys[6]]);
        }

        if (array_key_exists($keys[7], $arr)) {
            $this->setAppSlaExceeded($arr[$keys[7]]);
        }

        if (array_key_exists($keys[8], $arr)) {
            $this->setAppSlaPenValue($arr[$keys[8]]);
        }

        if (array_key_exists($keys[9], $arr)) {
            $this->setAppSlaStatus($arr[$keys[9]]);
        }

    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return     Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(AppSlaPeer::DATABASE_NAME);

        if ($this->isColumnModified(AppSlaPeer::APP_UID)) {
            $criteria->add(AppSlaPeer::APP_UID, $this->app_uid);
        }

        if ($this->isColumnModified(AppSlaPeer::SLA_UID)) {
            $criteria->add(AppSlaPeer::SLA_UID, $this->sla_uid);
        }

        if ($this->isColumnModified(AppSlaPeer::APP_SLA_INIT_DATE)) {
            $criteria->add(AppSlaPeer::APP_SLA_INIT_DATE, $this->app_sla_init_date);
        }

        if ($this->isColumnModified(AppSlaPeer::APP_SLA_DUE_DATE)) {
            $criteria->add(AppSlaPeer::APP_SLA_DUE_DATE, $this->app_sla_due_date);
        }

        if ($this->isColumnModified(AppSlaPeer::APP_SLA_FINISH_DATE)) {
            $criteria->add(AppSlaPeer::APP_SLA_FINISH_DATE, $this->app_sla_finish_date);
        }

        if ($this->isColumnModified(AppSlaPeer::APP_SLA_DURATION)) {
            $criteria->add(AppSlaPeer::APP_SLA_DURATION, $this->app_sla_duration);
        }

        if ($this->isColumnModified(AppSlaPeer::APP_SLA_REMAINING)) {
            $criteria->add(AppSlaPeer::APP_SLA_REMAINING, $this->app_sla_remaining);
        }

        if ($this->isColumnModified(AppSlaPeer::APP_SLA_EXCEEDED)) {
            $criteria->add(AppSlaPeer::APP_SLA_EXCEEDED, $this->app_sla_exceeded);
        }

        if ($this->isColumnModified(AppSlaPeer::APP_SLA_PEN_VALUE)) {
            $criteria->add(AppSlaPeer::APP_SLA_PEN_VALUE, $this->app_sla_pen_value);
        }

        if ($this->isColumnModified(AppSlaPeer::APP_SLA_STATUS)) {
            $criteria->add(AppSlaPeer::APP_SLA_STATUS, $this->app_sla_status);
        }


        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @return     Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = new Criteria(AppSlaPeer::DATABASE_NAME);

        $criteria->add(AppSlaPeer::APP_UID, $this->app_uid);
        $criteria->add(AppSlaPeer::SLA_UID, $this->sla_uid);

        return $criteria;
    }

    /**
     * Returns the composite primary key for this object.
     * The array elements will be in same order as specified in XML.
     * @return     array
     */
    public function getPrimaryKey()
    {
        $pks = array();

        $pks[0] = $this->getAppUid();

        $pks[1] = $this->getSlaUid();

        return $pks;
    }

    /**
     * Set the [composite] primary key.
     *
     * @param      array $keys The elements of the composite key (order must match the order in XML file).
     * @return     void
     */
    public function setPrimaryKey($keys)
    {

        $this->setAppUid($keys[0]);

        $this->setSlaUid($keys[1]);

    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of AppSla (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @throws     PropelException
     */
    public function copyInto($copyObj, $deepCopy = false)
    {

        $copyObj->setAppSlaInitDate($this->app_sla_init_date);

        $copyObj->setAppSlaDueDate($this->app_sla_due_date);

        $copyObj->setAppSlaFinishDate($this->app_sla_finish_date);

        $copyObj->setAppSlaDuration($this->app_sla_duration);

        $copyObj->setAppSlaRemaining($this->app_sla_remaining);

        $copyObj->setAppSlaExceeded($this->app_sla_exceeded);

        $copyObj->setAppSlaPenValue($this->app_sla_pen_value);

        $copyObj->setAppSlaStatus($this->app_sla_status);


        $copyObj->setNew(true);

        $copyObj->setAppUid(''); // this is a pkey column, so set to default value

        $copyObj->setSlaUid(''); // this is a pkey column, so set to default value

    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return     AppSla Clone of current object.
     * @throws     PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);
        return $copyObj;
    }

    /**
     * Returns a peer instance associated with this om.
     *
     * Since Peer classes are not to have any instance attributes, this method returns the
     * same instance for all member of this class. The method could therefore
     * be static, but this would prevent one from overriding the behavior.
     *
     * @return     AppSlaPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new AppSlaPeer();
        }
        return self::$peer;
    }
}

