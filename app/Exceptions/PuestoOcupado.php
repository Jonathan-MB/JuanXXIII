<?php

namespace App\Exceptions;

use RuntimeException;

//  La mesa y la silla que se escribieron ya son de otra persona (o de
// alguien que se fue: esa silla queda vacía). Deshace la transacción.
class PuestoOcupado extends RuntimeException
{
}
