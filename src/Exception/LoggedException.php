<?php

namespace parseword\pickset\Exception;

/**
 * A custom Exception which will attempt to write the message and call stack to
 * a log file (via parseword\pickset\Logger), then re-raise the exception for
 * for further handling. This allows exceptions to be tracked even when they're
 * caught or accidentally swallowed.
 *
 * *****************************************************************************
 * This file is part of pickset, a collection of PHP utilities.
 *
 * Copyright 2006, 2019 Shaun Cummiskey <shaun@shaunc.com> <https://shaunc.com/>
 *
 * Repository: <https://github.com/parseword/pickset/>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
class LoggedException extends \Exception
{

    /**
     * Attempt to write the exception to the log file, then raise it again.
     *
     * @param type $message
     * @param type $code
     * @param \Throwable $previous
     */
    public function __construct($message = '', $code = 0,
            \Throwable $previous = null) {
        \parseword\pickset\Logger::error($message . ' : ' . $this->getTraceAsString());
        parent::__construct($message, $code, $previous);
    }

}
