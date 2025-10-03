<?php

namespace App\Enum;

enum OperatorType: string
{
  case Equal = '=';
  case NotEqual = '!=';
  case GreaterThan = '>';
  case LessThan = '<';
  case GreaterOrEqual = '>=';
  case LessOrEqual = '<=';
  case And = 'AND';
  case Or = 'OR';
  case In = 'IN';
  case NotIn = 'NOT_IN';
  case Plus = '+';
  case Minus = '-';
  case Multiply = '*';
  case Divide = '/';
  case LParen = '(';
  case RParen = ')';
}
