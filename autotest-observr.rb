
def clear_console
  puts "\e[H\e[2J"
end

watch('lib/.*\.php') do |match|
  clear_console
  system 'phpunit'
end
