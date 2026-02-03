<?php

declare(strict_types=1);

/**
 * Copyright (c) Florian Krämer (https://florian-kraemer.net)
 * Licensed under The GPL License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Florian Krämer (https://florian-kraemer.net)
 * @author    Florian Krämer
 * @link      https://github.com/Phauthentic
 * @license   https://opensource.org/licenses/GPL-3.0 GPL License
 */

namespace Phauthentic\BcCheck\ValueObject;

enum BcBreakType: string
{
    case ClassRemoved = 'CLASS_REMOVED';
    case ClassMadeFinal = 'CLASS_MADE_FINAL';
    case ClassMadeAbstract = 'CLASS_MADE_ABSTRACT';
    case InterfaceRemoved = 'INTERFACE_REMOVED';
    case ParentChanged = 'PARENT_CHANGED';
    case MethodRemoved = 'METHOD_REMOVED';
    case MethodRenamed = 'METHOD_RENAMED';
    case MethodSignatureChanged = 'METHOD_SIGNATURE_CHANGED';
    case MethodReturnTypeChanged = 'METHOD_RETURN_TYPE_CHANGED';
    case MethodVisibilityReduced = 'METHOD_VISIBILITY_REDUCED';
    case MethodMadeFinal = 'METHOD_MADE_FINAL';
    case MethodMadeStatic = 'METHOD_MADE_STATIC';
    case MethodMadeNonStatic = 'METHOD_MADE_NON_STATIC';
    case MethodMadeAbstract = 'METHOD_MADE_ABSTRACT';
    case PropertyRemoved = 'PROPERTY_REMOVED';
    case PropertyRenamed = 'PROPERTY_RENAMED';
    case PropertyVisibilityReduced = 'PROPERTY_VISIBILITY_REDUCED';
    case PropertyTypeChanged = 'PROPERTY_TYPE_CHANGED';
    case PropertyMadeReadonly = 'PROPERTY_MADE_READONLY';
    case PropertyMadeStatic = 'PROPERTY_MADE_STATIC';
    case PropertyMadeNonStatic = 'PROPERTY_MADE_NON_STATIC';
    case ConstantRemoved = 'CONSTANT_REMOVED';
    case ConstantVisibilityReduced = 'CONSTANT_VISIBILITY_REDUCED';
    case Other = 'OTHER';
}
