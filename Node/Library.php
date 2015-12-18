<?php

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2015, Hoa community. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Hoa nor the names of its contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS AND CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Hoa\Protocol\Node;

/**
 * Class \Hoa\Protocol\Node\Library.
 *
 * `hoa://Library/` node.
 *
 * @copyright  Copyright © 2007-2015 Hoa community
 * @license    New BSD License
 */
class Library extends Node
{
    /**
     * Queue of the component.
     *
     * @param   string  $queue    Queue of the component (generally, a filename,
     *                            with probably a query).
     * @return  mixed
     */
    public function reach($queue = null)
    {
        if (!WITH_COMPOSER) {
            return parent::reach($queue);
        }

        if (!empty($queue)) {
            $head = $queue;

            if (false !== $pos = strpos($queue, '/')) {
                $head  = substr($head, 0, $pos);
                $queue = DIRECTORY_SEPARATOR . substr($queue, $pos + 1);
            } else {
                $queue = null;
            }

            $out = [];

            foreach (explode(RS, $this->_reach) as $part) {
                $out[] = "\r" . $part . strtolower($head) . $queue;
            }

            $out[] = "\r" . dirname(dirname(dirname(__DIR__))) . $queue;

            return implode(RS, $out);
        }

        $out = [];

        foreach (explode(RS, $this->_reach) as $part) {
            $pos   = strrpos(rtrim($part, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR) + 1;
            $head  = substr($part, 0, $pos);
            $tail  = substr($part, $pos);
            $out[] = $head . strtolower($tail);
        }

        $this->_reach = implode(RS, $out);

        return parent::reach($queue);
    }
}
