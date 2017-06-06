require 'grape'
require 'rsolr'

require 'active_support/inflector'

require_relative 'conf.rb'

Dir.glob(File.expand_path('../helpers/*.rb', __dir__)).each do |file|
  require_relative file
end

Dir.glob(File.expand_path('../models/*.rb', __dir__)).each do |file|
  require_relative file
end
