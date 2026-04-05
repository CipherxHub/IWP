#!/usr/bin/perl
# ============================================================
# Practical 11 - Perl Array Operations
# ============================================================
# To run: perl practical11_perl_arrays.pl
# ============================================================

use strict;
use warnings;

print "=" x 55, "\n";
print "    Practical 11 - Perl Array Operations\n";
print "=" x 55, "\n\n";

# ============================================================
# (a) Create an array with 10 elements
# ============================================================
print "--- (a) Create array with 10 elements ---\n";

my @arr = (10, 20, 30, 40, 50, 60, 70, 80, 90, 100);

print "Array: @arr\n\n";

# ============================================================
# (b) Print the highest index of the array
# ============================================================
print "--- (b) Highest index of the array ---\n";

# $#arr gives the last/highest index of array @arr
print "Highest index (\$#arr): $#arr\n\n";

# ============================================================
# (c) Assign beyond the end of the array (at index 20)
# ============================================================
print "--- (c) Assign element at index 20 (beyond end) ---\n";

$arr[20] = 999;

# Perl auto-fills in-between elements with undef
print "Assigned value 999 at index 20\n";
print "Array length is now: ", scalar(@arr), " elements\n";
print "New highest index: $#arr\n\n";

# ============================================================
# (d) Save the current highest index in a scalar, print it
# ============================================================
print "--- (d) Save highest index in scalar ---\n";

my $saved_high_index = $#arr;

print "Saved highest index into \$saved_high_index = $saved_high_index\n\n";

# ============================================================
# (e) Set the array size to 5 elements (index 4)
# ============================================================
print "--- (e) Shrink array to 5 elements (index 4) ---\n";

$#arr = 4;    # Setting $#arr shrinks (or grows) the array

print "Set \$#arr = 4\n";
print "Array size now: ", scalar(@arr), " elements\n";
print "Array: @arr\n\n";

# ============================================================
# (f) Print the array
# ============================================================
print "--- (f) Print the array ---\n";

# Print each element with its index
for my $i (0 .. $#arr) {
    my $val = defined $arr[$i] ? $arr[$i] : "undef";
    print "  arr[$i] = $val\n";
}
print "\n";

# ============================================================
# (g) Set the array size BACK to the previously saved size
# ============================================================
print "--- (g) Restore array size using saved index ---\n";

$#arr = $saved_high_index;

print "Restored \$#arr = $saved_high_index\n";
print "Array size now: ", scalar(@arr), " elements\n\n";

# ============================================================
# (h) Print the array (after restore)
# ============================================================
print "--- (h) Print the array after restoring size ---\n";

for my $i (0 .. $#arr) {
    my $val = defined $arr[$i] ? $arr[$i] : "undef";
    print "  arr[$i] = $val\n";
}

print "\n";
print "Note: Elements that were deleted in step (e) come\n";
print "back as 'undef' (undefined) after restoring size.\n";
print "Only the 5 original elements retain their values.\n\n";

print "=" x 55, "\n";
print "    End of Practical 11\n";
print "=" x 55, "\n";
