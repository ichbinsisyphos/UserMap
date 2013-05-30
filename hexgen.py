#!/usr/bin/env python

import math


def hexgen(startx, starty, mindist):
	corners = [ (round(math.cos(angle*math.pi/180.),3),round(math.sin(angle*math.pi/180.),3)) for angle in range(0,360,60) ]
	layer = 1
	interpolate = 0
	layerstart = 1
	index = 1

	while True:
		perlayer = layer*6
		if perlayer < 1: perlayer = 1

		if index == perlayer + layerstart:
			layerstart = index
			layer += 1
			perlayer = layer*6
		
		interpolate = layer - 1
		if interpolate < 0: interpolate = 0
		layerindex = index - layerstart

		if interpolate < 1 or not layerindex%(interpolate+1):
			cornerindex = layerindex/(interpolate+1)
			corner = "corner " + str(cornerindex)
			x,y = corners[cornerindex]
			x *= mindist/2.*layer
			y *= mindist*layer

		else:
			interpolateindex = layerindex%(interpolate + 1) 
			prevcornerindex = layerindex/(interpolate + 1)
			nextcornerindex = prevcornerindex + 1
			if nextcornerindex > 5:
				nextcornerindex = 0
			prevx,prevy = corners[prevcornerindex]
			nextx,nexty = corners[nextcornerindex]

			diffx = nextx - prevx
			diffy = nexty - prevy

			x = mindist/2.*layer*(prevx + diffx*interpolateindex/(interpolate+1))
			y = mindist*layer*(prevy + diffy*interpolateindex/(interpolate+1))

		index += 1
		yield (startx+x,starty+y)
