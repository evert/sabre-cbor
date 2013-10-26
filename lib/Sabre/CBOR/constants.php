<?php

namespace Sabre\CBOR;

/**
 * The data type is specified using 1 byte.
 *
 * The first 3 bits of this byte denotes the 'major type'.
 * The following list of constants denotes this major type. The major types in
 * rfc7049 have much lower values (2 instead of 0x20 for negative int, for
 * instance). This is because we already shifted the value by 5 bits to the
 * left.
 */

/**
 * Positive integers
 */
const TYPE_UNSIGNED_INT = 0x00;

/**
 * Negative integers
 */
const TYPE_NEGATIVE_INT = 0x20;

/**
 * List of bytes.
 */
const TYPE_BYTE_STRING  = 0x40;

/**
 * UTF-8 strings.
 */
const TYPE_TEXT_STRING  = 0x60;

/**
 * Array, or a list of items.
 */
const TYPE_ARRAY        = 0x80;

/**
 * An object. Something with a key and a value.
 */
const TYPE_MAP          = 0xa0;

/**
 * Semantic tag.
 */
const TYPE_TAG          = 0xc0;

/**
 * The last major type encodes simple types such as true, false and null and
 * floating point numbers. We're calling is MISC.
 */
const TYPE_MISC         = 0xe0;
