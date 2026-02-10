<?php

namespace App\Enums;

enum UserRole: string {
  case User = 'user';
  case Originator = 'originator';
  case Coordinator = 'coordinator';
  case Superior = 'superior';
  case Admin = 'admin';
}