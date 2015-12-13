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

namespace Hoa\Protocol;

use Hoa\Consistency;

/**
 * Class \Hoa\Protocol\Protocol.
 *
 * Root of the `hoa://` protocol.
 *
 * @author     Ivan Enderlin <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright © 2007-2015 Ivan Enderlin.
 * @license    New BSD License
 */
class Protocol extends Node
{
    /**
     * No resolution value.
     *
     * @const string
     */
    const NO_RESOLUTION = '/hoa/flatland';

    /**
     * Singleton.
     *
     * @var \Hoa\Protocol\Protocol
     */
    private static $_instance = null;



    /**
     * Initialize the protocol.
     *
     * @return void
     */
    public function __construct()
    {
        $this->initialize();

        return;
    }

    /**
     * Singleton.
     * To use the `hoa://` protocol shared by everyone.
     *
     * @return  \HoaProtocol\Protocol;
     */
    public static function getInstance()
    {
        if (null === static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

    /**
     * Initialize the protocol.
     *
     * @return  void
     */
    protected function initialize()
    {
        $root = dirname(dirname(__DIR__));
        $cwd  =
            'cli' === PHP_SAPI
                ? dirname(realpath($_SERVER['argv'][0]))
                : getcwd();

        $this[] = new Node(
            'Application',
            $cwd,
            [
                new Node('Public', 'Public' . DS)
            ]
        );

        $this[] = new Node(
            'Data',
            dirname($cwd) . DS,
            [
                new Node(
                    'Etc',
                    'Etc' . DS,
                    [
                        new Node('Configuration', 'Configuration' . DS),
                        new Node('Locale', 'Locale' . DS)
                    ]
                ),
                new Node('Lost+found', 'Lost+found' . DS),
                new Node('Temporary', 'Temporary' . DS),
                new Node(
                    'Variable',
                    'Variable' . DS,
                    [
                        new Node('Cache', 'Cache' . DS),
                        new Node('Database', 'Database' . DS),
                        new Node('Log', 'Log' . DS),
                        new Node('Private', 'Private' . DS),
                        new Node('Run', 'Run' . DS),
                        new Node('Test', 'Test' . DS)
                    ]
                )
            ]
        );

        $this[] = new Node\Library(
            'Library',
            $root . DS . 'Hoathis' . DS . RS .
            $root . DS . 'Hoa' . DS
        );

        return;
    }
}

/**
 * Flex entity.
 */
Consistency::flexEntity('Hoa\Protocol\Protocol');
